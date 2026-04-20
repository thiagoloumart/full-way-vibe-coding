<?php

declare(strict_types=1);

namespace App\Domain\Notificacao\Exceptions;

use RuntimeException;

/**
 * Falha definitiva — não retentar.
 *
 * Categorias:
 *   - `numero-invalido` — telefone rejeitado pelo provedor (formato ou inexistente).
 *   - `paciente-sem-canal` — paciente anonimizado ou `telefone_whatsapp = null` (P-03).
 *   - `template-rejeitado` — Meta reclassificou ou suspendeu o template de utility.
 *   - `auth-falhou` — credencial expirada/revogada.
 *
 * Cada motivo dispara comportamento distinto no job chamador:
 *   - numero-invalido / paciente-sem-canal → evento `lembrete_numero_invalido`, sem retry;
 *   - template-rejeitado → alerta crítico ao admin (crit. invalidação de D-002);
 *   - auth-falhou → alerta crítico ao admin.
 *
 * Origem: FR-012 · C-003 · P-03 · D-002 crit. invalidação.
 */
final class FalhaDefinitivaException extends RuntimeException
{
    public const MOTIVO_NUMERO_INVALIDO = 'numero-invalido';
    public const MOTIVO_PACIENTE_SEM_CANAL = 'paciente-sem-canal';
    public const MOTIVO_TEMPLATE_REJEITADO = 'template-rejeitado';
    public const MOTIVO_AUTH_FALHOU = 'auth-falhou';
    public const MOTIVO_OUTRO = 'outro';

    public function __construct(
        string $message,
        public readonly string $motivoCategoria = self::MOTIVO_OUTRO,
        public readonly ?int $httpStatus = null,
        public readonly ?string $corpoResposta = null,
    ) {
        parent::__construct($message);
    }
}
