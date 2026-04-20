<?php

declare(strict_types=1);

namespace App\Infra\Notificacao;

use App\Domain\Notificacao\Contracts\NotificacaoDriver;
use App\Domain\Notificacao\Valores\IdempotencyKey;
use App\Domain\Notificacao\Valores\RetornoDriver;
use App\Models\Paciente;
use Illuminate\Support\Facades\Log;

/**
 * Driver no-op para staging e rollback emergencial.
 *
 * Ativado via `WHATSAPP_DRIVER=noop`. Ideal para:
 *   - staging sem conta Meta configurada;
 *   - rollback emergencial se template Meta for suspenso (crit. invalidação D-002);
 *   - testes feature onde rede externa é indesejável.
 *
 * Retorna sempre sucesso com `enviadoReal=false` e `idExterno` fake baseado
 * na IdempotencyKey (permite reconciliação simulada de callbacks).
 *
 * Origem: plano §9 rollback · ADR-L-001 §Plano de reversão.
 */
final class NoopDriver implements NotificacaoDriver
{
    public function nome(): string
    {
        return 'noop';
    }

    public function enviar(
        Paciente $paciente,
        array $parametrosTemplate,
        IdempotencyKey $idempotencyKey,
    ): RetornoDriver {
        Log::info('NoopDriver: envio simulado', [
            'paciente_id' => $paciente->id,
            'idempotency_key' => (string) $idempotencyKey,
        ]);

        return new RetornoDriver(
            idExterno: 'noop-'.md5((string) $idempotencyKey),
            driverNome: 'noop',
            enviadoReal: false,
            retornoProvedor: ['simulated' => true],
        );
    }
}
