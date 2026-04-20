<?php

declare(strict_types=1);

namespace App\Domain\Lgpd\Exceptions;

use RuntimeException;

/**
 * Tentativa de anonimização violou um invariante.
 *
 * Origem: C-003 · FR-033 · D-E-01.
 */
final class AnonimizacaoProibidaException extends RuntimeException
{
    public static function usuarioSemPermissao(int $userId): self
    {
        return new self(
            "Anonimização LGPD exige is_admin=true (user_id={$userId} não tem). Viola D-E-01 / C-002."
        );
    }

    public static function motivoObrigatorio(): self
    {
        return new self(
            'Motivo textual obrigatório (C-003 · TipoEvento::Anonimizacao->exigeMotivo()).'
        );
    }

    public static function jaAnonimizado(int $pacienteId): self
    {
        return new self(
            "Paciente {$pacienteId} já está anonimizado; operação é irreversível."
        );
    }
}
