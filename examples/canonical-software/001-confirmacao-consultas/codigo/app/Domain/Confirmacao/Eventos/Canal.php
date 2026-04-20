<?php

declare(strict_types=1);

namespace App\Domain\Confirmacao\Eventos;

/**
 * Canal por onde o evento entrou no sistema.
 *
 * Origem: constitution v1.1 §3 · C-006 · FR-018.
 *
 * Nota: o canal `whatsapp` abstrai o provedor concreto (Meta Cloud API ou
 * Z-API) — D-E-02 preserva o contrato abstrato. O driver concreto é
 * escolha de Camada 2 (ADR-L-001).
 */
enum Canal: string
{
    /** Entrada ou saída via WhatsApp (independe do driver concreto). */
    case Whatsapp = 'whatsapp';

    /** Ação humana pela UI autenticada do painel. */
    case ManualPeloPainel = 'manual-pelo-painel';

    /** Derivação automática interna (scheduler, listener, guard). */
    case SistemaAutomacao = 'sistema-automacao';
}
