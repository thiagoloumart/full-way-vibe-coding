<?php

declare(strict_types=1);

namespace App\Domain\Confirmacao\Services;

use Carbon\CarbonImmutable;

/**
 * Helper puro: calcula o próximo momento enviável respeitando a janela
 * operacional (C-004 padrão 08h–20h BRT) e o deadline (horário da consulta).
 *
 * Regras:
 *   1. Se `agora` está dentro da janela [inicio, fim] → retorna `agora`.
 *   2. Se `agora` está antes do início → retorna hoje às `inicio`.
 *   3. Se `agora` está depois do fim → retorna amanhã às `inicio`.
 *   4. Se o momento calculado ≥ `deadline` → retorna `null` (desistir: envio
 *      ultrapassaria o próprio horário da consulta, fallback humano).
 *
 * Materializa D-E-06 da constituição: envios **proibidos** fora da janela.
 *
 * Origem: FR-010 · C-004 · D-E-06.
 */
final class RespeitaJanelaOperacional
{
    /**
     * @param  CarbonImmutable  $agora  — ponto de avaliação.
     * @param  int  $horaInicio  — 0..23 (padrão 8).
     * @param  int  $horaFim     — 0..23 (padrão 20, limite superior inclusivo).
     * @param  CarbonImmutable  $deadline — horário da consulta; envio NÃO pode ser após.
     */
    public function proximoMomentoEnviavel(
        CarbonImmutable $agora,
        int $horaInicio,
        int $horaFim,
        CarbonImmutable $deadline,
    ): ?CarbonImmutable {
        $this->guardArgumentos($horaInicio, $horaFim);

        $candidato = match (true) {
            $agora->hour >= $horaInicio && $agora->hour < $horaFim => $agora,
            $agora->hour < $horaInicio => $agora->setTime($horaInicio, 0, 0),
            default /* hora ≥ horaFim */ => $agora->addDay()->setTime($horaInicio, 0, 0),
        };

        if ($candidato->greaterThanOrEqualTo($deadline)) {
            return null;
        }

        return $candidato;
    }

    private function guardArgumentos(int $horaInicio, int $horaFim): void
    {
        if ($horaInicio < 0 || $horaInicio > 23) {
            throw new \InvalidArgumentException("horaInicio fora de 0..23: {$horaInicio}");
        }
        if ($horaFim <= $horaInicio || $horaFim > 24) {
            throw new \InvalidArgumentException(
                "horaFim deve ser > horaInicio e ≤ 24; recebido {$horaFim} (inicio={$horaInicio})"
            );
        }
    }
}
