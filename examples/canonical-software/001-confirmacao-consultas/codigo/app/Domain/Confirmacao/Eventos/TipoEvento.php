<?php

declare(strict_types=1);

namespace App\Domain\Confirmacao\Eventos;

/**
 * Enumeração fechada de tipos de evento do histórico imutável de uma Consulta.
 *
 * Origem: constitution v1.1 §3 (histórico imutável) · C-005 · D-E-03 · FR-017.
 *
 * Todo tipo novo DEVE ser adicionado aqui com ADR local minor (Camada 2 §8);
 * adicionar sem ADR viola D-E-03.
 *
 * Após Fase 6 Analyze: sem-canal adicionado para edge case "paciente sem
 * WhatsApp ativo" (P-03).
 */
enum TipoEvento: string
{
    // ---------- Lifecycle da Consulta (agendamento manual do atendente) ----------
    case Criada = 'criada';
    case Editada = 'editada';
    case CanceladaClinica = 'cancelada_clinica';
    case SemCanal = 'sem_canal'; // paciente sem telefone → fallback humano imediato

    // ---------- Lembrete (lifecycle da automação) ----------
    case LembreteAgendado = 'lembrete_agendado';
    case LembreteEnviado = 'lembrete_enviado';
    case LembreteFalhaEnvio = 'lembrete_falha_envio';
    case LembreteNumeroInvalido = 'lembrete_numero_invalido';

    // ---------- Respostas do paciente via WhatsApp ----------
    case RespostaRecebidaConfirmar = 'resposta_recebida_confirmar';
    case RespostaRecebidaCancelar = 'resposta_recebida_cancelar';
    case RespostaRecebidaReagendar = 'resposta_recebida_reagendar';
    case RespostaAmbigua = 'resposta_ambigua';

    // ---------- Derivações automáticas ----------
    case StatusSemResposta = 'status_sem_resposta'; // T-060 scheduler

    // ---------- Marcações finais ----------
    case Compareceu = 'compareceu';
    case NoShow = 'no_show';

    // ---------- Meta-eventos ----------
    case Correcao = 'correcao'; // C-005 · exige motivo + ref_evento_id
    case Anonimizacao = 'anonimizacao'; // C-003 · exige motivo

    /**
     * Tipos que, quando presentes como último evento efetivo, tornam a Consulta
     * terminal (não mais elegível para transição automática).
     */
    public function eTerminal(): bool
    {
        return match ($this) {
            self::Compareceu,
            self::NoShow,
            self::CanceladaClinica,
            self::RespostaRecebidaCancelar => true,
            default => false,
        };
    }

    /**
     * Tipos que exigem campo `motivo` obrigatório no evento (C-005, C-003).
     */
    public function exigeMotivo(): bool
    {
        return match ($this) {
            self::Correcao, self::Anonimizacao => true,
            default => false,
        };
    }

    /**
     * Tipos que exigem `ref_evento_id` apontando para o evento corrigido.
     */
    public function exigeReferenciaEvento(): bool
    {
        return $this === self::Correcao;
    }
}
