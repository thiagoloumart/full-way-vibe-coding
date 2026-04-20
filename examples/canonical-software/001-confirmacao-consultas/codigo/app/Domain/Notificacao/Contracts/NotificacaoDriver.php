<?php

declare(strict_types=1);

namespace App\Domain\Notificacao\Contracts;

use App\Domain\Notificacao\Exceptions\FalhaDefinitivaException;
use App\Domain\Notificacao\Exceptions\FalhaTransitoriaException;
use App\Domain\Notificacao\Valores\IdempotencyKey;
use App\Domain\Notificacao\Valores\RetornoDriver;
use App\Models\Paciente;

/**
 * Contrato abstrato de canal de notificação.
 *
 * **Invariante estrutural D-E-02** (constitution v1.1 §10): o domínio de
 * Confirmação nunca depende de provedor concreto. Toda implementação
 * específica (Meta Cloud API, Z-API, Evolution API, Noop) vive atrás
 * desta interface.
 *
 * Escolha do adaptador ativo é Camada 2 (env `WHATSAPP_DRIVER`), formalizada
 * em ADR-L-001. Mudar driver em produção é troca de 1 sprint de infra, sem
 * tocar `app/Domain/`.
 *
 * Origem: FR-007 · D-002 · ADR-L-001 · D-E-02.
 */
interface NotificacaoDriver
{
    /**
     * Envia uma mensagem de template ao paciente pelo canal configurado.
     *
     * @param  Paciente  $paciente  — destinatário. Se `anonimizado_em` não-nulo
     *                              OU `telefone_whatsapp` null, o adaptador
     *                              DEVE lançar FalhaDefinitivaException
     *                              com `motivo = 'paciente-sem-canal'`.
     * @param  array<string,string>  $parametrosTemplate  — substituições do template
     *                                                    aprovado pelo provedor.
     * @param  IdempotencyKey  $idempotencyKey  — garante não-duplicação em reenvio.
     *
     * @throws FalhaTransitoriaException  — erro recuperável (5xx, timeout, 429).
     *                                     Job chamador DEVE retentar com backoff
     *                                     conforme C-004 (3 tentativas 5/15/45 min).
     * @throws FalhaDefinitivaException   — erro permanente (número inválido,
     *                                     template rejeitado, paciente anonimizado).
     *                                     NÃO retentar.
     */
    public function enviar(
        Paciente $paciente,
        array $parametrosTemplate,
        IdempotencyKey $idempotencyKey,
    ): RetornoDriver;

    /**
     * Nome interno do driver (`meta` | `zapi` | `evolution` | `noop`).
     * Usado em logs e no modelo Notificacao para auditoria.
     */
    public function nome(): string;
}
