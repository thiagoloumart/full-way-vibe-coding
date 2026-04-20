<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\URL;

/**
 * Consulta médica — unidade central do módulo.
 *
 * `status_cache` é **projeção** derivada dos eventos; source-of-truth fica em
 * `EventoConsulta` + `DerivarStatus` (DT-02). Se divergir, comando
 * `consultas:reconciliar-status-cache` (T-062) reconcilia.
 *
 * `janela_*_horas_usada` são **snapshot** no momento da criação (FR-029) —
 * alteração futura da config da Clínica NÃO afeta consultas já criadas.
 *
 * Origem: FR-003 a FR-029 · spec.md Key Entities.
 *
 * @property int $id
 * @property int $clinica_id
 * @property int $paciente_id
 * @property int $medico_id
 * @property \Illuminate\Support\Carbon $datahora_agendada
 * @property string|null $status_cache
 * @property int|null $criado_por_user_id
 * @property int $janela_lembrete_horas_usada
 * @property int $janela_silencio_horas_usada
 */
final class Consulta extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinica_id',
        'paciente_id',
        'medico_id',
        'datahora_agendada',
        'status_cache',
        'criado_por_user_id',
        'janela_lembrete_horas_usada',
        'janela_silencio_horas_usada',
    ];

    protected $casts = [
        'datahora_agendada' => 'datetime',
        'janela_lembrete_horas_usada' => 'integer',
        'janela_silencio_horas_usada' => 'integer',
    ];

    // ========================================================================
    // RELAÇÕES
    // ========================================================================

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(EventoConsulta::class);
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_user_id');
    }

    // ========================================================================
    // DERIVAÇÕES
    // ========================================================================

    /**
     * URL pública assinada enviada ao paciente no lembrete.
     *
     * Expira junto ao término da janela de silêncio (buffer de 1h para
     * resposta tardia). Não exige login. Mostra detalhes somente-leitura.
     *
     * Materializa FR-034 + T-061.
     */
    public function linkPublicoAssinado(): string
    {
        $expiracao = $this->datahora_agendada
            ->copy()
            ->subHours($this->janela_silencio_horas_usada)
            ->addHour(); // buffer

        return URL::temporarySignedRoute(
            name: 'consulta.publico',
            expiration: $expiracao,
            parameters: ['consulta' => $this->id],
        );
    }
}
