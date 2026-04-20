<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Confirmacao\Jobs\ProcessarCallbackWhatsappJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Endpoint de webhook para o provedor de WhatsApp (Meta Cloud API).
 *
 *   GET  /webhooks/whatsapp  — verificação de subscription (challenge/response).
 *   POST /webhooks/whatsapp  — recepção de eventos (mensagens recebidas,
 *                              entregues, lidas, falhadas).
 *
 * Regra: **responder 200 imediato** e enfileirar processamento assíncrono.
 * Meta penaliza webhooks lentos com unsubscribe automático após timeouts
 * repetidos.
 *
 * Origem: FR-013 · NFR-001 · NFR-006.
 */
final class WhatsappWebhookController
{
    /**
     * Verificação inicial do webhook (GET).
     * Meta envia ?hub.mode=subscribe&hub.challenge=XXX&hub.verify_token=SEU_TOKEN
     * e espera receber o `challenge` de volta se o token bater.
     */
    public function verify(Request $request): Response
    {
        $modo = $request->query('hub_mode');
        $challenge = $request->query('hub_challenge');
        $token = $request->query('hub_verify_token');

        $tokenEsperado = config('services.whatsapp.meta.webhook_verify_token');

        if ($modo === 'subscribe' && $token === $tokenEsperado) {
            return new Response((string) $challenge, 200);
        }

        Log::warning('whatsapp webhook verify: token inválido', ['ip' => $request->ip()]);

        return new Response('forbidden', 403);
    }

    /**
     * Recebe eventos do provedor.
     *
     * Retorna 200 imediatamente mesmo em payload malformado (exceto falha
     * grave). Processamento vai para fila.
     */
    public function receive(Request $request): JsonResponse
    {
        $payload = $request->all();

        try {
            ProcessarCallbackWhatsappJob::dispatch($payload);
        } catch (\Throwable $e) {
            // NÃO propagar erro para Meta — só logar e retornar 200.
            Log::error('whatsapp webhook dispatch falhou', [
                'erro' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
        }

        return new JsonResponse(['received' => true]);
    }
}
