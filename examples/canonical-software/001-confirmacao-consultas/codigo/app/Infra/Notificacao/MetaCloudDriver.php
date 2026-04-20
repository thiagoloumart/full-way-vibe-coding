<?php

declare(strict_types=1);

namespace App\Infra\Notificacao;

use App\Domain\Notificacao\Contracts\NotificacaoDriver;
use App\Domain\Notificacao\Exceptions\FalhaDefinitivaException;
use App\Domain\Notificacao\Exceptions\FalhaTransitoriaException;
use App\Domain\Notificacao\Valores\IdempotencyKey;
use App\Domain\Notificacao\Valores\RetornoDriver;
use App\Models\Paciente;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Adaptador concreto para Meta Cloud API (WhatsApp Business Platform).
 *
 * Escolhido como provedor default em ADR-L-001. Z-API fica como implementação
 * irmã para contingência; Evolution API pode ser adicionada via outro
 * adaptador implementando o mesmo contrato (D-E-02).
 *
 * Origem: FR-006 a FR-012 · C-004 · ADR-L-001.
 */
final class MetaCloudDriver implements NotificacaoDriver
{
    public function nome(): string
    {
        return 'meta';
    }

    public function __construct(
        /** ex.: `v19.0/{phone_number_id}` — lido em config/services.php. */
        private readonly string $apiBaseUrl,
        private readonly string $accessToken,
        private readonly string $phoneNumberId,
        private readonly string $templateLembreteName,
        private readonly string $templateLembreteLang,
        /** timeout do HTTP request em segundos. */
        private readonly int $timeoutSegundos = 10,
    ) {}

    public function enviar(
        Paciente $paciente,
        array $parametrosTemplate,
        IdempotencyKey $idempotencyKey,
    ): RetornoDriver {
        $this->guardPacienteTemCanal($paciente);

        $payload = $this->montarPayload($paciente, $parametrosTemplate);

        try {
            $response = $this->cliente($idempotencyKey)->post(
                sprintf('%s/%s/messages', $this->apiBaseUrl, $this->phoneNumberId),
                $payload,
            );
        } catch (ConnectionException $e) {
            throw new FalhaTransitoriaException(
                'Falha de conexão com Meta Cloud API: '.$e->getMessage(),
            );
        }

        if ($response->successful()) {
            return $this->parseRetornoSucesso($response);
        }

        $this->traduzirErro($response);
    }

    /**
     * Cliente HTTP com auth Bearer e header Idempotency-Key.
     *
     * Nota: Meta aceita header `Idempotency-Key` em endpoints de mensagens
     * conforme docs 2025+ (`https://developers.facebook.com/docs/whatsapp/...`)
     * `[INFERÊNCIA]` — validar na execução real (dívida 7.8).
     */
    private function cliente(IdempotencyKey $idempotencyKey): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.$this->accessToken,
            'Content-Type' => 'application/json',
            'Idempotency-Key' => (string) $idempotencyKey,
        ])->timeout($this->timeoutSegundos)->acceptJson();
    }

    /**
     * @param  array<string,string>  $parametrosTemplate
     * @return array<string,mixed>
     */
    private function montarPayload(Paciente $paciente, array $parametrosTemplate): array
    {
        $componentsBody = [
            'type' => 'body',
            'parameters' => array_map(
                static fn (string $valor): array => ['type' => 'text', 'text' => $valor],
                array_values($parametrosTemplate),
            ),
        ];

        return [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->normalizarE164($paciente->telefone_whatsapp),
            'type' => 'template',
            'template' => [
                'name' => $this->templateLembreteName,
                'language' => ['code' => $this->templateLembreteLang],
                'components' => [$componentsBody],
            ],
        ];
    }

    private function guardPacienteTemCanal(Paciente $paciente): void
    {
        if ($paciente->anonimizado_em !== null) {
            throw new FalhaDefinitivaException(
                'Paciente anonimizado (C-003); envio bloqueado.',
                FalhaDefinitivaException::MOTIVO_PACIENTE_SEM_CANAL,
            );
        }

        if (blank($paciente->telefone_whatsapp)) {
            throw new FalhaDefinitivaException(
                'Paciente sem telefone_whatsapp (P-03); envio bloqueado.',
                FalhaDefinitivaException::MOTIVO_PACIENTE_SEM_CANAL,
            );
        }
    }

    private function normalizarE164(string $telefone): string
    {
        // Remove tudo que não é dígito; assume BR +55 se não tiver DDI.
        $apenasDigitos = preg_replace('/\D+/', '', $telefone) ?? '';
        if (strlen($apenasDigitos) === 11) {
            return '55'.$apenasDigitos;
        }

        return $apenasDigitos;
    }

    private function parseRetornoSucesso(Response $response): RetornoDriver
    {
        $json = $response->json();

        $idExterno = $json['messages'][0]['id']
            ?? throw new FalhaTransitoriaException('Resposta Meta sem message id');

        return new RetornoDriver(
            idExterno: $idExterno,
            driverNome: 'meta',
            enviadoReal: true,
            retornoProvedor: $json,
        );
    }

    /**
     * Traduz resposta de erro em exceção tipada conforme C-004 + P-03.
     *
     * Categorias Meta conhecidas:
     *   - 131026 `(recipient unknown)` → definitiva numero-invalido.
     *   - 131047 `(re-engagement window)` → definitiva sem-canal.
     *   - 131051/131052 `(message type)` → transitória (pode ser regional).
     *   - 132000+ `(template)` → definitiva template-rejeitado.
     *   - 401/403 → definitiva auth-falhou.
     *   - 429/5xx → transitória.
     *
     * @throws FalhaDefinitivaException
     * @throws FalhaTransitoriaException
     */
    private function traduzirErro(Response $response): never
    {
        $status = $response->status();
        $body = $response->json();
        $metaCode = $body['error']['code'] ?? null;
        $mensagemMeta = $body['error']['message'] ?? 'erro sem mensagem';

        // Auth falhou — definitiva.
        if (in_array($status, [401, 403], true)) {
            throw new FalhaDefinitivaException(
                "Meta auth falhou (HTTP {$status}): {$mensagemMeta}",
                FalhaDefinitivaException::MOTIVO_AUTH_FALHOU,
                $status,
                $response->body(),
            );
        }

        // Template rejeitado — definitiva, aciona crit. invalidação D-002.
        if (is_int($metaCode) && $metaCode >= 132000 && $metaCode < 133000) {
            throw new FalhaDefinitivaException(
                "Meta template rejeitado (code {$metaCode}): {$mensagemMeta}",
                FalhaDefinitivaException::MOTIVO_TEMPLATE_REJEITADO,
                $status,
                $response->body(),
            );
        }

        // Número inválido — definitiva.
        if (in_array($metaCode, [131026, 131047, 131051], true)) {
            throw new FalhaDefinitivaException(
                "Meta número inválido (code {$metaCode}): {$mensagemMeta}",
                FalhaDefinitivaException::MOTIVO_NUMERO_INVALIDO,
                $status,
                $response->body(),
            );
        }

        // Rate limit Meta ou 5xx → transitória.
        if ($status === 429 || ($status >= 500 && $status < 600)) {
            throw new FalhaTransitoriaException(
                "Meta transitório (HTTP {$status}): {$mensagemMeta}",
                $status,
                $response->body(),
            );
        }

        // 4xx desconhecido — tratar como definitivo conservador.
        throw new FalhaDefinitivaException(
            "Meta erro desconhecido (HTTP {$status}, code {$metaCode}): {$mensagemMeta}",
            FalhaDefinitivaException::MOTIVO_OUTRO,
            $status,
            $response->body(),
        );
    }
}
