<?php

declare(strict_types=1);

namespace App\Domain\Notificacao\Exceptions;

use RuntimeException;

/**
 * Falha transitória do provedor — job chamador DEVE retentar.
 *
 * Sinaliza erros recuperáveis: timeout, 5xx, 429, provedor temporariamente
 * indisponível. Política de retry em C-004 (3 tentativas com backoff 5/15/45 min).
 *
 * Origem: FR-011 · C-004.
 */
final class FalhaTransitoriaException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $httpStatus = null,
        public readonly ?string $corpoResposta = null,
    ) {
        parent::__construct($message);
    }
}
