<?php

declare(strict_types=1);

namespace App\Domain\Notificacao\Valores;

/**
 * Retorno de um envio bem-sucedido de notificação (via NotificacaoDriver).
 *
 * `idExterno` é a chave pela qual webhooks de resposta serão reconciliados
 * (FR-013). Sem `idExterno`, reconciliação é impossível — por isso não é
 * nullable.
 *
 * Origem: FR-013 · NFR-006.
 */
final readonly class RetornoDriver
{
    public function __construct(
        /** ID da mensagem no provedor externo — chave de reconciliação. */
        public string $idExterno,

        /** Nome do driver que gerou o retorno (`meta`, `zapi`, `noop`, ...). */
        public string $driverNome,

        /** Se o envio foi **real** (ou simulado em NoopDriver). */
        public bool $enviadoReal,

        /** Resposta raw do provedor, útil para auditoria. */
        public array $retornoProvedor = [],
    ) {}
}
