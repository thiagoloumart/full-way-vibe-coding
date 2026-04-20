<?php

declare(strict_types=1);

namespace App\Domain\Confirmacao\Services;

use App\Domain\Confirmacao\Eventos\TipoEvento;
use App\Models\EventoConsulta;

/**
 * Reducer puro: deriva o status atual de uma Consulta a partir da sequência
 * de eventos imutáveis no histórico.
 *
 * Source of truth do status. O campo `consultas.status_cache` é uma
 * projeção descartável mantida por listener (DT-02); se desincronizar,
 * o comando `consultas:reconciliar-status-cache` (T-062) reconstrói.
 *
 * Regra de correção (C-005): eventos do tipo `correcao` são aplicados
 * sobre o evento referenciado por `ref_evento_id`, sobrepondo-o no
 * cálculo do status final sem removê-lo do histórico.
 *
 * Origem: FR-017 · C-005.
 */
final class DerivarStatus
{
    /**
     * Resolve o status da Consulta dado o ID.
     *
     * Retorna string do `TipoEvento` que representa o status atual, ou
     * 'agendada' se nenhum evento ainda foi registrado.
     */
    public function executar(int $consultaId): string
    {
        /** @var \Illuminate\Support\Collection<int,EventoConsulta> $eventos */
        $eventos = EventoConsulta::query()
            ->where('consulta_id', $consultaId)
            ->orderBy('criado_em')
            ->orderBy('id')
            ->get();

        if ($eventos->isEmpty()) {
            return 'agendada';
        }

        return $this->aplicarReducer($eventos);
    }

    /**
     * @param  \Illuminate\Support\Collection<int,EventoConsulta>  $eventos
     */
    private function aplicarReducer($eventos): string
    {
        // Primeiro passo: mapear correções sobre eventos referenciados.
        // O efeito de um evento `correcao` é **substituir** o status
        // derivado pelo novo TipoEvento indicado no payload.
        $statusCorrigidoPorEventoId = [];

        foreach ($eventos as $evento) {
            if ($evento->tipo !== TipoEvento::Correcao) {
                continue;
            }

            $novoStatus = $evento->payload_extra['novo_status'] ?? null;
            if ($novoStatus === null || $evento->ref_evento_id === null) {
                continue;
            }

            $statusCorrigidoPorEventoId[$evento->ref_evento_id] = $novoStatus;
        }

        // Segundo passo: percorrer eventos em ordem cronológica, aplicando
        // cada tipo. Quando achar um evento cujo id_original foi
        // corrigido, usar o novo status ao invés do tipo original.
        $statusAtual = 'agendada';

        foreach ($eventos as $evento) {
            if ($evento->tipo === TipoEvento::Correcao) {
                continue; // Não aplicar correção como evento em si — apenas
                          // via sobreposição no evento referenciado.
            }

            if (isset($statusCorrigidoPorEventoId[$evento->id])) {
                $statusAtual = $statusCorrigidoPorEventoId[$evento->id];
                continue;
            }

            $statusAtual = $this->mapearEventoParaStatus($evento->tipo) ?? $statusAtual;
        }

        return $statusAtual;
    }

    /**
     * Mapeia TipoEvento para string de status público da Consulta.
     *
     * Retorna `null` para tipos que NÃO alteram o status (ex: anonimizacao,
     * resposta_ambigua que aparece lateral mas não muda status operacional).
     */
    private function mapearEventoParaStatus(TipoEvento $tipo): ?string
    {
        return match ($tipo) {
            TipoEvento::Criada => 'agendada',
            TipoEvento::Editada => null, // mantém status anterior
            TipoEvento::CanceladaClinica => 'cancelada-pela-clinica',
            TipoEvento::SemCanal => 'sem-canal',

            TipoEvento::LembreteAgendado => null, // interno, não muda status público
            TipoEvento::LembreteEnviado => 'lembrete-enviado',
            TipoEvento::LembreteFalhaEnvio => 'falha-envio',
            TipoEvento::LembreteNumeroInvalido => 'numero-invalido',

            TipoEvento::RespostaRecebidaConfirmar => 'confirmada',
            TipoEvento::RespostaRecebidaCancelar => 'cancelada-pelo-paciente',
            TipoEvento::RespostaRecebidaReagendar => 'reagendamento-solicitado',
            TipoEvento::RespostaAmbigua => null, // não muda status, só sinaliza no painel

            TipoEvento::StatusSemResposta => 'sem-resposta',

            TipoEvento::Compareceu => 'compareceu',
            TipoEvento::NoShow => 'no-show',

            TipoEvento::Correcao, TipoEvento::Anonimizacao => null,
        };
    }
}
