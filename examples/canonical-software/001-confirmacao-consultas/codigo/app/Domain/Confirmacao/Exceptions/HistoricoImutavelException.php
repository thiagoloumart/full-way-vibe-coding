<?php

declare(strict_types=1);

namespace App\Domain\Confirmacao\Exceptions;

use RuntimeException;

/**
 * Violação do invariante D-E-03 da constituição (histórico append-only).
 *
 * Lançada quando:
 *   - código tenta `EventoConsulta::update()` ou `::delete()` diretamente;
 *   - aplicação detecta tentativa de mutação antes do banco (Observer).
 *
 * Contrapartida no banco: trigger PG `BEFORE UPDATE OR DELETE ON
 * eventos_consulta` que também lança exceção SQL (defense-in-depth DT-03).
 *
 * Correções legítimas DEVEM ser feitas via INSERT de evento `correcao` com
 * `ref_evento_id` + `motivo` obrigatório (C-005).
 */
final class HistoricoImutavelException extends RuntimeException
{
    public static function tentativaDeUpdate(string $eventoId): self
    {
        return new self(
            "Histórico imutável (D-E-03): tentativa de UPDATE no evento {$eventoId} bloqueada. "
            .'Correções usam novo evento `correcao` com ref_evento_id (ver C-005).'
        );
    }

    public static function tentativaDeDelete(string $eventoId): self
    {
        return new self(
            "Histórico imutável (D-E-03): tentativa de DELETE no evento {$eventoId} bloqueada. "
            .'DELETE é proibido; para anonimização LGPD, use AnonimizarPaciente (C-003).'
        );
    }

    public static function motivoObrigatorio(string $tipo): self
    {
        return new self(
            "Evento do tipo `{$tipo}` exige campo `motivo` não vazio (C-005 / C-003)."
        );
    }

    public static function referenciaEventoObrigatoria(): self
    {
        return new self(
            'Evento do tipo `correcao` exige campo `ref_evento_id` apontando para o evento corrigido (C-005).'
        );
    }
}
