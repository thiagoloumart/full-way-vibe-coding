<?php

declare(strict_types=1);

namespace App\Domain\Confirmacao\Jobs;

use App\Domain\Confirmacao\Eventos\AtorTipo;
use App\Domain\Confirmacao\Eventos\Canal;
use App\Domain\Confirmacao\Eventos\TipoEvento;
use App\Domain\Confirmacao\Services\RegistrarEvento;
use App\Models\Consulta;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Detecta consultas cujo lembrete foi enviado mas o paciente ainda não
 * respondeu, e cuja janela de silêncio já esgotou. Para cada match, emite
 * evento `status_sem_resposta` → listener atualiza `status_cache` →
 * painel destaca → D-E-05 materializado.
 *
 * Scheduler: a cada 15 minutos (`->everyFifteenMinutes()` no Console\Kernel).
 *
 * Idempotente: consultas que já têm status derivado ≠ `lembrete-enviado`
 * são ignoradas naturalmente pelo WHERE.
 *
 * Origem: D-E-05 · FR-021 · C-004 · T-060.
 */
final class DetectarSemRespostaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(RegistrarEvento $registrar): void
    {
        Consulta::query()
            ->where('status_cache', 'lembrete-enviado')
            ->where('datahora_agendada', '>=', now()) // consulta ainda no futuro
            ->whereRaw(
                "datahora_agendada - (janela_silencio_horas_usada || ' hours')::interval < NOW()"
            )
            ->chunkById(100, function ($consultas) use ($registrar): void {
                foreach ($consultas as $consulta) {
                    $registrar->executar(
                        consultaId: $consulta->id,
                        tipo: TipoEvento::StatusSemResposta,
                        atorTipo: AtorTipo::SistemaAutomacao,
                        atorId: null,
                        canal: Canal::SistemaAutomacao,
                    );
                }
            });
    }
}
