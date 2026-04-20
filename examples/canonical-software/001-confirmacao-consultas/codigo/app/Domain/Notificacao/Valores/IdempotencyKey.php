<?php

declare(strict_types=1);

namespace App\Domain\Notificacao\Valores;

/**
 * Chave de idempotência para disparo de notificação.
 *
 * Construída determinísticamente a partir de `consulta_id` + `janela_id`
 * (hash da janela planejada). Envios repetidos com a mesma chave dentro
 * do TTL do guard NÃO geram mensagem duplicada (FR-009, NFR-006).
 *
 * Origem: FR-009 · NFR-006 · D-E-06.
 */
final readonly class IdempotencyKey
{
    public function __construct(public string $valor)
    {
        if (strlen($valor) < 8 || strlen($valor) > 128) {
            throw new \InvalidArgumentException(
                'IdempotencyKey deve ter entre 8 e 128 caracteres (recebido: '.strlen($valor).').'
            );
        }
    }

    public static function paraLembreteDeConsulta(int $consultaId, int $janelaTimestamp): self
    {
        return new self(sprintf('lembrete:%d:%d', $consultaId, $janelaTimestamp));
    }

    public function __toString(): string
    {
        return $this->valor;
    }
}
