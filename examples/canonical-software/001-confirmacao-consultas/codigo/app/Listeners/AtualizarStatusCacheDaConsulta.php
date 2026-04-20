<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Confirmacao\Services\DerivarStatus;
use App\Models\Consulta;
use App\Models\EventoConsulta;
use Illuminate\Support\Facades\Log;

/**
 * Atualiza a projeção `consultas.status_cache` após cada novo EventoConsulta.
 *
 * `status_cache` é uma projeção derivada, **nunca** source-of-truth. Se falhar
 * transiente, o comando `consultas:reconciliar-status-cache` (T-062) reconcilia.
 *
 * Eventos que **não alteram** o status (`Editada`, `LembreteAgendado`,
 * `RespostaAmbigua`, `Correcao` puro sem payload válido, `Anonimizacao`)
 * ainda disparam o listener, mas o reducer retorna o mesmo status anterior.
 * Gravar mesmo assim é barato e idempotente.
 *
 * Origem: DT-02 · FR-017 · C-005.
 */
final class AtualizarStatusCacheDaConsulta
{
    public function __construct(
        private readonly DerivarStatus $derivarStatus,
    ) {}

    /**
     * Recebe o EventoConsulta recém-criado (dispatched via
     * `Eloquent::created` evento do model no EventServiceProvider).
     */
    public function handle(EventoConsulta $evento): void
    {
        $statusNovo = $this->derivarStatus->executar($evento->consulta_id);

        $consulta = Consulta::query()->find($evento->consulta_id);
        if ($consulta === null) {
            Log::warning('status_cache skip: consulta ausente', [
                'evento_id' => $evento->id,
                'consulta_id' => $evento->consulta_id,
            ]);

            return;
        }

        if ($consulta->status_cache === $statusNovo) {
            return; // idempotente
        }

        $consulta->status_cache = $statusNovo;
        $consulta->save();
    }
}
