<?php

declare(strict_types=1);

namespace App\Domain\Confirmacao\Guards;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;

/**
 * Lock idempotente Redis para disparo de lembrete.
 *
 * Chave: `lembrete:{consulta_id}:{janela_timestamp}`.
 * TTL: 1h (cobre a janela útil do lembrete; expira sozinho se job falhar).
 *
 * Materializa FR-009 (idempotência do disparo) + NFR-006 (idempotência geral).
 *
 * Uso:
 * ```
 * if (! $this->guard->adquirir($consulta->id, $janelaTimestamp)) {
 *     return; // outro processo já disparou; aborta silenciosamente.
 * }
 * ```
 */
final class IdempotenciaLembreteGuard
{
    public function __construct(
        private readonly CacheRepository $cache,
        private readonly int $ttlSegundos = 3600,
    ) {}

    public static function comCacheDefault(int $ttlSegundos = 3600): self
    {
        return new self(Cache::store('redis'), $ttlSegundos);
    }

    public function adquirir(int $consultaId, int $janelaTimestamp): bool
    {
        $chave = $this->montarChave($consultaId, $janelaTimestamp);

        // Laravel `Cache::add(...)` retorna true só se a chave ainda não existia
        // (análogo a Redis SETNX).
        return $this->cache->add($chave, now()->toIso8601String(), $this->ttlSegundos);
    }

    public function liberar(int $consultaId, int $janelaTimestamp): void
    {
        $this->cache->forget($this->montarChave($consultaId, $janelaTimestamp));
    }

    private function montarChave(int $consultaId, int $janelaTimestamp): string
    {
        return "lembrete:{$consultaId}:{$janelaTimestamp}";
    }
}
