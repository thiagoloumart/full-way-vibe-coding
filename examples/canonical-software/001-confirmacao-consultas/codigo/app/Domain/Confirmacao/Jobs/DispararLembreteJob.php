<?php

declare(strict_types=1);

namespace App\Domain\Confirmacao\Jobs;

use App\Domain\Confirmacao\Eventos\AtorTipo;
use App\Domain\Confirmacao\Eventos\Canal;
use App\Domain\Confirmacao\Eventos\TipoEvento;
use App\Domain\Confirmacao\Guards\IdempotenciaLembreteGuard;
use App\Domain\Confirmacao\Guards\RateLimitClinicaGuard;
use App\Domain\Confirmacao\Services\RegistrarEvento;
use App\Domain\Notificacao\Contracts\NotificacaoDriver;
use App\Domain\Notificacao\Exceptions\FalhaDefinitivaException;
use App\Domain\Notificacao\Exceptions\FalhaTransitoriaException;
use App\Domain\Notificacao\Valores\IdempotencyKey;
use App\Models\Consulta;
use App\Models\Notificacao;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Dispara o lembrete de uma Consulta pelo NotificacaoDriver ativo.
 *
 * Pipeline:
 *   1. Guard: paciente não anonimizado, não `sem-canal`.
 *   2. Guard idempotência Redis (FR-009).
 *   3. Guard rate-limit por clínica (DT-08).
 *   4. Chamar `NotificacaoDriver::enviar(...)`.
 *   5. Persistir `Notificacao` + evento `lembrete_enviado` em transação.
 *   6. Traduzir exceções em eventos de falha e (se transitório) retentar.
 *
 * Retry (FR-011, C-004): Laravel automático com `$tries=3` e `$backoff=[300, 900, 2700]`
 * (5, 15, 45 min em segundos).
 *
 * Origem: FR-006 a FR-012 · NFR-006 · NFR-007.
 */
final class DispararLembreteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Máximo de tentativas para falhas transitórias (C-004).
     */
    public int $tries = 3;

    /**
     * Backoff em segundos por tentativa (5min / 15min / 45min).
     */
    public array $backoff = [300, 900, 2700];

    public function __construct(
        public readonly int $consultaId,
        /** Timestamp da janela de envio — compõe a chave de idempotência. */
        public readonly int $janelaTimestamp,
    ) {}

    public function handle(
        NotificacaoDriver $driver,
        RegistrarEvento $registrarEvento,
        IdempotenciaLembreteGuard $idempotencia,
        RateLimitClinicaGuard $rateLimit,
    ): void {
        $consulta = Consulta::query()
            ->with(['paciente', 'clinica'])
            ->find($this->consultaId);

        if ($consulta === null) {
            Log::warning('DispararLembreteJob: consulta ausente', [
                'consulta_id' => $this->consultaId,
            ]);

            return;
        }

        // R-04: guard final anti-race com anonimização.
        if ($consulta->paciente?->anonimizado_em !== null) {
            Log::info('DispararLembreteJob: paciente anonimizado em trânsito; cancelando envio', [
                'consulta_id' => $this->consultaId,
            ]);
            $this->emitirNumeroInvalido($registrarEvento, $this->consultaId, 'paciente anonimizado antes do envio');

            return;
        }

        if (blank($consulta->paciente?->telefone_whatsapp)) {
            // P-03: paciente sem canal. Fallback humano imediato.
            $this->emitirNumeroInvalido($registrarEvento, $this->consultaId, 'paciente sem telefone_whatsapp (P-03)');

            return;
        }

        $idempotencyKey = IdempotencyKey::paraLembreteDeConsulta($this->consultaId, $this->janelaTimestamp);

        if (! $idempotencia->adquirir($this->consultaId, $this->janelaTimestamp)) {
            Log::info('DispararLembreteJob: lock idempotência já detido; aborta', [
                'consulta_id' => $this->consultaId,
            ]);

            return;
        }

        if (! $rateLimit->passa($consulta->clinica_id, CarbonImmutable::now())) {
            // Solta o lock e reagenda 1min à frente — padrão backpressure.
            $idempotencia->liberar($this->consultaId, $this->janelaTimestamp);
            $this->release(60);

            return;
        }

        try {
            $retorno = $driver->enviar(
                paciente: $consulta->paciente,
                parametrosTemplate: $this->montarParametros($consulta),
                idempotencyKey: $idempotencyKey,
            );
        } catch (FalhaTransitoriaException $e) {
            // Solta o lock: retry vai readquirir; idempotência do provedor
            // (via header) evita duplicação real.
            $idempotencia->liberar($this->consultaId, $this->janelaTimestamp);

            $registrarEvento->executar(
                consultaId: $this->consultaId,
                tipo: TipoEvento::LembreteFalhaEnvio,
                atorTipo: AtorTipo::SistemaAutomacao,
                atorId: null,
                canal: Canal::Whatsapp,
                payloadExtra: [
                    'erro' => $e->getMessage(),
                    'http_status' => $e->httpStatus,
                    'tentativa' => $this->attempts(),
                ],
            );

            // Relança para o Laravel retentar com backoff.
            throw $e;
        } catch (FalhaDefinitivaException $e) {
            // Definitiva → registra e NÃO relança (para não retentar).
            $registrarEvento->executar(
                consultaId: $this->consultaId,
                tipo: TipoEvento::LembreteNumeroInvalido,
                atorTipo: AtorTipo::SistemaAutomacao,
                atorId: null,
                canal: Canal::Whatsapp,
                payloadExtra: [
                    'motivo_categoria' => $e->motivoCategoria,
                    'mensagem' => $e->getMessage(),
                    'http_status' => $e->httpStatus,
                ],
            );

            return;
        }

        // Sucesso: persiste Notificacao + evento lembrete_enviado.
        \DB::transaction(function () use ($retorno, $registrarEvento): void {
            $evento = $registrarEvento->executar(
                consultaId: $this->consultaId,
                tipo: TipoEvento::LembreteEnviado,
                atorTipo: AtorTipo::SistemaAutomacao,
                atorId: null,
                canal: Canal::Whatsapp,
                payloadExtra: ['driver' => $retorno->driverNome, 'enviado_real' => $retorno->enviadoReal],
                idExternoProvedor: $retorno->idExterno,
            );

            Notificacao::create([
                'evento_consulta_id' => $evento->id,
                'driver' => $retorno->driverNome,
                'id_externo' => $retorno->idExterno,
                'status_entrega' => 'enviado',
                'retorno_provedor' => $retorno->retornoProvedor,
                'tentativa' => $this->attempts(),
            ]);
        });
    }

    private function emitirNumeroInvalido(RegistrarEvento $registrar, int $consultaId, string $motivo): void
    {
        $registrar->executar(
            consultaId: $consultaId,
            tipo: TipoEvento::LembreteNumeroInvalido,
            atorTipo: AtorTipo::SistemaAutomacao,
            atorId: null,
            canal: Canal::SistemaAutomacao,
            payloadExtra: ['motivo' => $motivo],
        );
    }

    /**
     * @return array<string,string>
     */
    private function montarParametros(Consulta $consulta): array
    {
        $dataHora = $consulta->datahora_agendada->setTimezone('America/Sao_Paulo');

        return [
            'clinica_nome' => $consulta->clinica->nome,
            'medico_nome' => $consulta->medico->nome,
            'data_consulta' => $dataHora->format('d/m/Y'),
            'hora_consulta' => $dataHora->format('H:i'),
            // URL pública assinada (T-061, gerada pelo controller do webhook agendador).
            'link_publico' => $consulta->linkPublicoAssinado(),
        ];
    }
}
