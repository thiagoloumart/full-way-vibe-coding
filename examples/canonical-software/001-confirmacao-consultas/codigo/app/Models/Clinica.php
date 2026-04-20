<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Clínica — entidade única no MVP (single-tenant, D-003).
 *
 * Armazena parâmetros operacionais configuráveis (C-004 defaults):
 *   - `janela_lembrete_horas`     — quando enviar lembrete antes da consulta (default 24h)
 *   - `janela_silencio_horas`     — após essa janela sem resposta, consulta vira `sem-resposta` (default 4h)
 *   - `envio_inicio_hora`         — hora do dia a partir da qual é permitido enviar (default 8h BRT)
 *   - `envio_fim_hora`            — hora do dia até a qual é permitido enviar (default 20h BRT)
 *   - `retry_max`                 — tentativas de retry em erro transitório (default 3)
 *
 * Alteração via `ClinicaConfigForm` (Livewire, T-044). Mudança **não afeta**
 * consultas já criadas (FR-029) — as consultas capturam snapshot em
 * `consultas.janela_lembrete_horas_usada` e `janela_silencio_horas_usada`
 * na criação.
 *
 * Origem: FR-028 · FR-029 · C-002 · C-004 · D-003.
 */
final class Clinica extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'janela_lembrete_horas',
        'janela_silencio_horas',
        'envio_inicio_hora',
        'envio_fim_hora',
        'retry_max',
    ];

    protected $casts = [
        'janela_lembrete_horas' => 'integer',
        'janela_silencio_horas' => 'integer',
        'envio_inicio_hora' => 'integer',
        'envio_fim_hora' => 'integer',
        'retry_max' => 'integer',
    ];

    // ========================================================================
    // RELAÇÕES
    // ========================================================================

    public function pacientes(): HasMany
    {
        return $this->hasMany(Paciente::class);
    }

    public function medicos(): HasMany
    {
        return $this->hasMany(Medico::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class);
    }
}
