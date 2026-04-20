<?php

declare(strict_types=1);

namespace App\Domain\Lgpd\Services;

use App\Domain\Confirmacao\Eventos\AtorTipo;
use App\Domain\Confirmacao\Eventos\Canal;
use App\Domain\Confirmacao\Eventos\TipoEvento;
use App\Domain\Confirmacao\Services\RegistrarEvento;
use App\Domain\Lgpd\Exceptions\AnonimizacaoProibidaException;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Anonimização LGPD art. 18 — regra §5.4 (C-003).
 *
 * **Operação sem rollback.** Uma vez executada, PII do paciente é sobrescrita
 * irreversivelmente. Integridade referencial preservada (consultas, eventos,
 * notificações continuam com FK válida, apenas com tombstone em PII).
 *
 * Garantias operacionais:
 *   - **Transação ACID** (DB::transaction): ou tudo é anonimizado e o evento
 *     `anonimizacao` é registrado, ou nada.
 *   - **Lock pessimista** no paciente (`SELECT ... FOR UPDATE`): previne
 *     race com `DispararLembreteJob` em trânsito (R-04 do plan).
 *   - **Exigência de admin**: apenas User com `is_admin=true` pode executar
 *     (validação de domínio; middleware `ExigeIsAdmin` já barra a UI).
 *   - **Evento `anonimizacao` imutável**: entra no histórico com motivo
 *     obrigatório (conforme TipoEvento::Anonimizacao->exigeMotivo()).
 *
 * Origem: FR-033 · C-003 · NFR-003 · R-04 · D-E-04.
 */
final class AnonimizarPaciente
{
    public function __construct(
        private readonly RegistrarEvento $registrarEvento,
    ) {}

    /**
     * Anonimiza o paciente dado pelo `pacienteId` sob autoridade do `admin`.
     *
     * @param  string  $motivo  — texto obrigatório (ex.: "LGPD art. 18 — solicitação paciente via e-mail 2026-04-15").
     *
     * @throws AnonimizacaoProibidaException  se admin não tem is_admin ou paciente já anonimizado.
     */
    public function executar(int $pacienteId, User $admin, string $motivo): void
    {
        if (! $admin->is_admin) {
            throw AnonimizacaoProibidaException::usuarioSemPermissao($admin->id);
        }

        if (trim($motivo) === '') {
            throw AnonimizacaoProibidaException::motivoObrigatorio();
        }

        DB::transaction(function () use ($pacienteId, $admin, $motivo): void {
            /** @var Paciente $paciente */
            $paciente = Paciente::query()
                ->where('id', $pacienteId)
                ->lockForUpdate() // SELECT ... FOR UPDATE — bloqueia race com jobs em trânsito.
                ->firstOrFail();

            if ($paciente->anonimizado_em !== null) {
                throw AnonimizacaoProibidaException::jaAnonimizado($pacienteId);
            }

            // 1) Registrar evento `anonimizacao` em **cada consulta** do paciente
            //    (trilha auditável em todos os históricos afetados).
            foreach ($paciente->consultas()->pluck('id') as $consultaId) {
                $this->registrarEvento->executar(
                    consultaId: $consultaId,
                    tipo: TipoEvento::Anonimizacao,
                    atorTipo: AtorTipo::Atendente,
                    atorId: $admin->id,
                    canal: Canal::ManualPeloPainel,
                    motivo: $motivo,
                );
            }

            // 2) Sobrescrever PII atomicamente.
            $hashCurto = substr(Str::ulid()->toBase32(), -8);

            $paciente->forceFill([
                'nome' => "paciente-excluido-{$hashCurto}",
                'telefone_whatsapp' => null,
                'email' => null,
                'anonimizado_em' => now(),
            ])->save();
        }, attempts: 1);
    }
}
