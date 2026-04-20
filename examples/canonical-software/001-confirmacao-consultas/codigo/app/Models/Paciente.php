<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo de paciente — PII sensível (NFR-003) + passível de anonimização (C-003).
 *
 * Campos `nome`, `telefone_whatsapp`, `email` são PII e DEVEM ser:
 *   - criptografados em repouso (cast `encrypted` se aplicável, ou coluna
 *     Postgres `pgcrypto`);
 *   - mascarados em logs (via `App\Logging\MascarararPiiProcessor`);
 *   - passíveis de anonimização atômica (UPDATE sem cascade delete).
 *
 * Após `AnonimizarPaciente::executar(...)`:
 *   - `anonimizado_em` ≠ null
 *   - `nome` = "paciente-excluido-<hash>"
 *   - `telefone_whatsapp` = null
 *   - `email` = null
 *   - consultas / eventos / notificações permanecem com FK intacta
 *
 * Origem: FR-001 · FR-033 · NFR-003 · C-003 · P-03.
 *
 * @property int $id
 * @property int $clinica_id
 * @property string $nome
 * @property string|null $telefone_whatsapp  — null se paciente não tem WhatsApp (P-03)
 *                                             ou já foi anonimizado (C-003).
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $anonimizado_em
 */
final class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinica_id',
        'nome',
        'telefone_whatsapp',
        'email',
    ];

    protected $casts = [
        'anonimizado_em' => 'datetime',
    ];

    protected $hidden = [
        // Oculta do JSON/array padrão para reduzir chance de leak acidental.
        'telefone_whatsapp',
        'email',
    ];

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class);
    }

    // ========================================================================
    // CONSULTAS DE ESTADO
    // ========================================================================

    public function estaAnonimizado(): bool
    {
        return $this->anonimizado_em !== null;
    }

    public function temCanalDeContato(): bool
    {
        return ! $this->estaAnonimizado()
            && ! blank($this->telefone_whatsapp);
    }
}
