<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Notificacao\Contracts\NotificacaoDriver;
use App\Infra\Notificacao\MetaCloudDriver;
use App\Infra\Notificacao\NoopDriver;
use App\Infra\Notificacao\ZApiDriver;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;

/**
 * Resolve o NotificacaoDriver ativo a partir da env `WHATSAPP_DRIVER`.
 *
 * Materializa D-E-02 — contrato abstrato com troca via env. Adicionar novo
 * adaptador (ex: EvolutionDriver) = nova classe em app/Infra/Notificacao/ +
 * novo `case` aqui. Nenhum arquivo de domínio muda.
 *
 * Origem: D-E-02 · ADR-L-001 · config/services.php.
 */
final class NotificacaoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificacaoDriver::class, function ($app): NotificacaoDriver {
            $driver = config('services.whatsapp.driver', 'noop');

            return match ($driver) {
                'meta' => $this->buildMetaDriver(),
                'zapi' => $this->buildZApiDriver(),
                'noop' => new NoopDriver,
                default => throw new BindingResolutionException(
                    "WHATSAPP_DRIVER inválido: {$driver}. Valores aceitos: meta, zapi, noop."
                ),
            };
        });
    }

    private function buildMetaDriver(): MetaCloudDriver
    {
        $cfg = config('services.whatsapp.meta');

        return new MetaCloudDriver(
            apiBaseUrl: $cfg['api_base_url'] ?? 'https://graph.facebook.com/v19.0',
            accessToken: $cfg['access_token'] ?? throw new BindingResolutionException(
                'META_ACCESS_TOKEN ausente no ambiente.'
            ),
            phoneNumberId: $cfg['phone_number_id'] ?? throw new BindingResolutionException(
                'META_PHONE_NUMBER_ID ausente.'
            ),
            templateLembreteName: $cfg['template_lembrete_name'] ?? 'lembrete_consulta_utility_v1',
            templateLembreteLang: $cfg['template_lembrete_lang'] ?? 'pt_BR',
            timeoutSegundos: (int) ($cfg['timeout_segundos'] ?? 10),
        );
    }

    private function buildZApiDriver(): ZApiDriver
    {
        // Driver irmão — implementação mínima stub (ADR-L-001 plano de reversão).
        return new ZApiDriver(
            instanceId: config('services.whatsapp.zapi.instance_id', ''),
            token: config('services.whatsapp.zapi.token', ''),
        );
    }
}
