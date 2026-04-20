<?php

declare(strict_types=1);

namespace App\Infra\Notificacao;

use App\Domain\Notificacao\Contracts\NotificacaoDriver;
use App\Domain\Notificacao\Exceptions\FalhaDefinitivaException;
use App\Domain\Notificacao\Valores\IdempotencyKey;
use App\Domain\Notificacao\Valores\RetornoDriver;
use App\Models\Paciente;

/**
 * Driver Z-API (não oficial) — **implementação irmã** para contingência.
 *
 * Conforme ADR-L-001 §Plano de reversão: se Meta suspender template
 * ou custo ultrapassar teto, `WHATSAPP_DRIVER=zapi` ativa este driver
 * em ~1 sprint de operação.
 *
 * **Estado atual:** stub contract-compliant. Implementação HTTP real fica
 * para execução do plano de reversão. O bom aqui é que contrato é igual
 * ao MetaCloudDriver (D-E-02) — troca é exclusivamente de infra.
 *
 * Origem: ADR-L-001 §Plano de reversão · D-E-02.
 */
final class ZApiDriver implements NotificacaoDriver
{
    public function __construct(
        private readonly string $instanceId,
        private readonly string $token,
    ) {}

    public function nome(): string
    {
        return 'zapi';
    }

    public function enviar(
        Paciente $paciente,
        array $parametrosTemplate,
        IdempotencyKey $idempotencyKey,
    ): RetornoDriver {
        if ($this->instanceId === '' || $this->token === '') {
            throw new FalhaDefinitivaException(
                'ZApiDriver não configurado — `ZAPI_INSTANCE_ID` e `ZAPI_TOKEN` ausentes.',
                FalhaDefinitivaException::MOTIVO_AUTH_FALHOU,
            );
        }

        // TODO (T-022 + plano de reversão ADR-L-001):
        //   - HTTP POST para `https://api.z-api.io/instances/{id}/token/{t}/send-text`
        //     (ou endpoint de template se ZApi suportar);
        //   - traduzir 4xx/5xx em FalhaTransitoria / FalhaDefinitiva
        //     seguindo mesma semântica do MetaCloudDriver;
        //   - header `idempotency-key` (se suportado) ou dedup próprio.
        throw new \LogicException(
            'ZApiDriver::enviar(): implementação pendente — ativar conforme '
            .'ADR-L-001 §Plano de reversão.'
        );
    }
}
