<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Confirmacao\Eventos\AtorTipo;
use App\Domain\Confirmacao\Eventos\Canal;
use App\Domain\Confirmacao\Eventos\TipoEvento;
use App\Domain\Confirmacao\Exceptions\HistoricoImutavelException;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Evento imutável do histórico de uma Consulta.
 *
 * Invariante estrutural D-E-03 (constitution v1.1 §10): append-only. Nunca
 * editar ou deletar. Correções são novos eventos do tipo `correcao` com
 * `ref_evento_id` + `motivo` obrigatório (C-005).
 *
 * Defesa em profundidade (DT-03):
 *   - Esta classe faz override de `update()` e `delete()` lançando exception;
 *   - Trigger PG `BEFORE UPDATE OR DELETE ON eventos_consulta` também barra
 *     qualquer SQL cru que escape do Eloquent.
 *
 * Origem: FR-017 · FR-018 · C-005 · C-006 · D-E-03.
 *
 * @property string $id ULID
 * @property int $consulta_id
 * @property TipoEvento $tipo
 * @property AtorTipo $ator_tipo
 * @property int|null $ator_id User responsável (null em sistema-automacao)
 * @property Canal $canal
 * @property string|null $id_externo_provedor
 * @property string|null $ip
 * @property string|null $motivo
 * @property array $payload_extra
 * @property string|null $ref_evento_id
 * @property \Illuminate\Support\Carbon $criado_em
 */
final class EventoConsulta extends Model
{
    use HasUlids;

    public $timestamps = false;

    public const CREATED_AT = 'criado_em';
    public const UPDATED_AT = null;

    protected $fillable = [
        'consulta_id',
        'tipo',
        'ator_tipo',
        'ator_id',
        'canal',
        'id_externo_provedor',
        'ip',
        'motivo',
        'payload_extra',
        'ref_evento_id',
    ];

    protected $casts = [
        'tipo' => TipoEvento::class,
        'ator_tipo' => AtorTipo::class,
        'canal' => Canal::class,
        'payload_extra' => 'array',
        'criado_em' => 'datetime',
    ];

    // ========================================================================
    // APPEND-ONLY GUARD — Eloquent level (DT-03 defense-in-depth)
    // ========================================================================

    /**
     * Bloqueia UPDATE via Eloquent. Trigger PG é a segunda barreira.
     */
    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw HistoricoImutavelException::tentativaDeUpdate($this->id);
        }

        // Guard de invariantes antes do INSERT (C-005).
        $this->guardInvariantesAntesDoInsert();

        return parent::save($options);
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        throw HistoricoImutavelException::tentativaDeUpdate($this->id ?? 'null');
    }

    public function delete(): ?bool
    {
        throw HistoricoImutavelException::tentativaDeDelete($this->id ?? 'null');
    }

    public function forceDelete(): ?bool
    {
        throw HistoricoImutavelException::tentativaDeDelete($this->id ?? 'null');
    }

    // ========================================================================
    // RELAÇÕES
    // ========================================================================

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }

    public function eventoReferenciado(): BelongsTo
    {
        return $this->belongsTo(self::class, 'ref_evento_id');
    }

    public function ator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ator_id');
    }

    // ========================================================================
    // INVARIANTES (C-005, C-003)
    // ========================================================================

    private function guardInvariantesAntesDoInsert(): void
    {
        $tipo = $this->tipo instanceof TipoEvento
            ? $this->tipo
            : TipoEvento::from($this->tipo);

        if ($tipo->exigeMotivo() && blank($this->motivo)) {
            throw HistoricoImutavelException::motivoObrigatorio($tipo->value);
        }

        if ($tipo->exigeReferenciaEvento() && blank($this->ref_evento_id)) {
            throw HistoricoImutavelException::referenciaEventoObrigatoria();
        }
    }
}
