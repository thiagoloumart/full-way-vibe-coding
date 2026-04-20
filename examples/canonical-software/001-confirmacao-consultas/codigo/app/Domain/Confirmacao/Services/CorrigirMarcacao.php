<?php

declare(strict_types=1);

namespace App\Domain\Confirmacao\Services;

use App\Domain\Confirmacao\Eventos\AtorTipo;
use App\Domain\Confirmacao\Eventos\Canal;
use App\Domain\Confirmacao\Eventos\TipoEvento;
use App\Domain\Confirmacao\Exceptions\HistoricoImutavelException;
use App\Models\EventoConsulta;
use App\Models\User;

/**
 * Correção de uma marcação de compareceu/no-show feita por engano (C-005).
 *
 * Regra: **nunca** editar o evento original. Cria novo evento `correcao`
 * com:
 *   - `ref_evento_id` apontando para o evento corrigido;
 *   - `motivo` textual obrigatório;
 *   - `payload_extra.novo_status` indicando o resultado ("compareceu" ou "no_show").
 *
 * O reducer `DerivarStatus` aplica a correção sobrepondo o status derivado
 * do evento original.
 *
 * Origem: FR-017 · C-005 · D-E-03.
 */
final class CorrigirMarcacao
{
    public function __construct(
        private readonly RegistrarEvento $registrarEvento,
    ) {}

    public function executar(
        string $eventoOriginalId,
        TipoEvento $novoStatus,
        User $ator,
        string $motivo,
    ): EventoConsulta {
        if (trim($motivo) === '') {
            throw HistoricoImutavelException::motivoObrigatorio('correcao');
        }

        if (! in_array($novoStatus, [TipoEvento::Compareceu, TipoEvento::NoShow], true)) {
            throw new \InvalidArgumentException(
                'CorrigirMarcacao só aceita TipoEvento::Compareceu ou ::NoShow como novo status.'
            );
        }

        /** @var EventoConsulta $original */
        $original = EventoConsulta::query()->findOrFail($eventoOriginalId);

        return $this->registrarEvento->executar(
            consultaId: $original->consulta_id,
            tipo: TipoEvento::Correcao,
            atorTipo: AtorTipo::Atendente,
            atorId: $ator->id,
            canal: Canal::ManualPeloPainel,
            payloadExtra: [
                'novo_status' => $novoStatus->value,
                'evento_original_tipo' => $original->tipo->value,
            ],
            motivo: $motivo,
            refEventoId: $original->id,
        );
    }
}
