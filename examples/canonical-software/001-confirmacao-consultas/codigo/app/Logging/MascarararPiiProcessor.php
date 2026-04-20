<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Remove PII de logs antes da persistência (NFR-003).
 *
 * Campos mascarados:
 *   - `telefone_whatsapp`, `telefone`, `phone` → últimos 4 dígitos + asteriscos.
 *   - `email` → primeiro caractere + `***@domínio`.
 *   - `nome`, `name` → mantém só iniciais.
 *   - `ip` → preserva octetos 1-2, mascara 3-4 (IPv4) ou últimos 64 bits (IPv6).
 *
 * Aplicação: registrado em `config/logging.php` como processor de todos
 * os canais de produção. Em dev, opcional via `LOG_MASK_PII=true`.
 *
 * Origem: NFR-003 · constitution §3 (PII) · LGPD minimização.
 */
final class MascarararPiiProcessor implements ProcessorInterface
{
    private const CHAVES_TELEFONE = ['telefone_whatsapp', 'telefone', 'phone'];
    private const CHAVES_NOME = ['nome', 'name'];

    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;
        $extra = $record->extra;

        return $record->with(
            context: $this->mascararArray($context),
            extra: $this->mascararArray($extra),
        );
    }

    /**
     * @param  array<string,mixed>  $dados
     * @return array<string,mixed>
     */
    private function mascararArray(array $dados): array
    {
        foreach ($dados as $chave => $valor) {
            $chaveBaixa = strtolower((string) $chave);

            if (is_array($valor)) {
                $dados[$chave] = $this->mascararArray($valor);

                continue;
            }

            if (! is_string($valor)) {
                continue;
            }

            if (in_array($chaveBaixa, self::CHAVES_TELEFONE, true)) {
                $dados[$chave] = $this->mascararTelefone($valor);
            } elseif (str_contains($chaveBaixa, 'email')) {
                $dados[$chave] = $this->mascararEmail($valor);
            } elseif (in_array($chaveBaixa, self::CHAVES_NOME, true)) {
                $dados[$chave] = $this->mascararNome($valor);
            } elseif ($chaveBaixa === 'ip') {
                $dados[$chave] = $this->mascararIp($valor);
            }
        }

        return $dados;
    }

    private function mascararTelefone(string $telefone): string
    {
        $digitos = preg_replace('/\D+/', '', $telefone) ?? '';
        if (strlen($digitos) < 4) {
            return '***';
        }

        return str_repeat('*', strlen($digitos) - 4).substr($digitos, -4);
    }

    private function mascararEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return '***';
        }

        [$local, $dominio] = explode('@', $email, 2);

        return (mb_substr($local, 0, 1) ?: '?').'***@'.$dominio;
    }

    private function mascararNome(string $nome): string
    {
        $partes = preg_split('/\s+/', trim($nome)) ?: [];

        return implode(' ', array_map(
            static fn (string $parte): string => (mb_substr($parte, 0, 1) ?: '?').'.',
            $partes,
        ));
    }

    private function mascararIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $partes = explode('.', $ip);
            $partes[2] = 'xxx';
            $partes[3] = 'xxx';

            return implode('.', $partes);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return preg_replace('/:[^:]+:[^:]+$/', ':xxxx:xxxx', $ip) ?? $ip;
        }

        return '***';
    }
}
