<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabela do histórico imutável de eventos por Consulta.
 *
 * Origem: FR-017 · FR-018 · C-005 · C-006 · D-E-03.
 *
 * Append-only: a barreira Eloquent está em App\Models\EventoConsulta.
 * A barreira SQL está na migration seguinte (trigger PG).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos_consulta', function (Blueprint $table): void {
            // ULID como PK para ordenação cronológica natural + base32 (DT-01).
            $table->ulid('id')->primary();

            $table->foreignId('consulta_id')
                ->constrained('consultas')
                ->restrictOnDelete();

            // enum como varchar (valores em App\Domain\Confirmacao\Eventos\TipoEvento).
            $table->string('tipo', 40);

            $table->string('ator_tipo', 24);
            $table->foreignId('ator_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('canal', 24);
            $table->string('id_externo_provedor', 80)->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('motivo')->nullable();

            // payload tipo-específico (ex.: `{novo_status:"compareceu"}` em Correcao).
            $table->jsonb('payload_extra')->default('{}');

            // referência ao evento corrigido (C-005).
            $table->ulid('ref_evento_id')->nullable();

            $table->timestampTz('criado_em')->useCurrent();

            $table->index(['consulta_id', 'criado_em']);
            $table->index(['id_externo_provedor']);

            $table->foreign('ref_evento_id')
                ->references('id')
                ->on('eventos_consulta')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_consulta');
    }
};
