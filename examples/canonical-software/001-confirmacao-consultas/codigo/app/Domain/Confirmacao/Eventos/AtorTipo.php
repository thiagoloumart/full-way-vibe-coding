<?php

declare(strict_types=1);

namespace App\Domain\Confirmacao\Eventos;

/**
 * Quem originou o evento do histórico.
 *
 * Origem: constitution v1.1 §3 (auditoria) · C-006 · FR-018.
 */
enum AtorTipo: string
{
    /** Paciente respondeu via WhatsApp (botão ou texto livre). */
    case Paciente = 'paciente';

    /** Atendente operou pela UI autenticada. */
    case Atendente = 'atendente';

    /** Sistema: job, scheduler, listener, anonimização temporal. */
    case SistemaAutomacao = 'sistema-automacao';
}
