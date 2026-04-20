<?php

declare(strict_types=1);

namespace App\Domain\Confirmacao\Services;

use App\Domain\Confirmacao\Eventos\AtorTipo;
use App\Domain\Confirmacao\Eventos\Canal;
use App\Domain\Confirmacao\Eventos\TipoEvento;
use App\Models\EventoConsulta;

/**
 * Ponto único de escrita no histórico imutável.
 *
 * Toda gravação de EventoConsulta no domínio DEVE passar por aqui. Testes
 * conferem que não há `EventoConsulta::create()` espalhados pelo código
 * fora deste service (exceto factories de teste).
 *
 * Após a criação, o listener AtualizarStatusCacheDaConsulta atualiza o
 * `status_cache` da Consulta (DT-02). Se o listener falhar, o comando
 * `consultas:reconciliar-status-cache` (T-062) reconcilia periodicamente.
 *
 * Origem: FR-017 · FR-018 · C-005 · C-006 · D-E-03.
 */
final class RegistrarEvento
{
    /**
     * @param  array<string,mixed>  $payloadExtra
     */
    public function executar(
        int $consultaId,
        TipoEvento $tipo,
        AtorTipo $atorTipo,
        ?int $atorId,
        Canal $canal,
        array $payloadExtra = [],
        ?string $idExternoProvedor = null,
        ?string $ip = null,
        ?string $motivo = null,
        ?string $refEventoId = null,
    ): EventoConsulta {
        $evento = new EventoConsulta([
            'consulta_id' => $consultaId,
            'tipo' => $tipo,
            'ator_tipo' => $atorTipo,
            'ator_id' => $atorId,
            'canal' => $canal,
            'id_externo_provedor' => $idExternoProvedor,
            'ip' => $ip,
            'motivo' => $motivo,
            'payload_extra' => $payloadExtra,
            'ref_evento_id' => $refEventoId,
        ]);

        // Guards de invariantes rodam dentro de `save()` do model
        // (HistoricoImutavelException em violações).
        $evento->save();

        // Dispara o evento Laravel "created" → listener atualiza status_cache.
        // (Eloquent dispara automaticamente; nada a fazer aqui.)

        return $evento;
    }
}
