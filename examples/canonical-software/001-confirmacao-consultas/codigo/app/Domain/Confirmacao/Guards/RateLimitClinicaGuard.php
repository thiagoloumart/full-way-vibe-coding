<?php

declare(strict_types=1);

namespace App\Domain\Confirmacao\Guards;

use Illuminate\Contracts\Redis\Connection as RedisConnection;
use Illuminate\Support\Facades\Redis;

/**
 * Token bucket por clínica para envios de notificação.
 *
 * Resolve o `[NEEDS CLARIFICATION]` residual da constituição v1.0 (§6
 * Regras de segurança estruturais) via DT-08 do plan: **50 msg/min/clínica**
 * padrão, configurável em `NOTIFICACAO_RATE_LIMIT_POR_CLINICA_MINUTO`.
 *
 * Implementação: `INCR` com TTL de 60s em chave `rate:clinica:{id}:{minuto}`.
 * Simples e exato ao minuto — não é sliding window, mas é suficiente para
 * perfil MPE e protege contra envio em massa acidental.
 *
 * Origem: DT-08 · constitution §6 · §10 D-E-06.
 */
final class RateLimitClinicaGuard
{
    public function __construct(
        private readonly RedisConnection $redis,
        private readonly int $limitePorMinuto = 50,
    ) {}

    public static function default(): self
    {
        return new self(
            Redis::connection(),
            (int) config('confirmacao.rate_limit_por_minuto', 50),
        );
    }

    /**
     * Tenta consumir 1 token do bucket da clínica.
     * Retorna `true` se passou no limit; `false` se esgotou.
     */
    public function passa(int $clinicaId, \DateTimeImmutable $agora): bool
    {
        $chave = sprintf(
            'rate:clinica:%d:%s',
            $clinicaId,
            $agora->format('Y-m-d-H-i'),
        );

        // Pipeline: INCR + EXPIRE (first-writer define TTL).
        $nova = (int) $this->redis->incr($chave);
        if ($nova === 1) {
            $this->redis->expire($chave, 60);
        }

        return $nova <= $this->limitePorMinuto;
    }
}
