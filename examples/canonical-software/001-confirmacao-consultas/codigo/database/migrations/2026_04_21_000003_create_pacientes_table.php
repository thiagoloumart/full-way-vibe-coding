<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cria tabela `pacientes`.
 *
 * - `telefone_whatsapp` é **nullable** (P-03 pós-Analyze): paciente sem
 *   WhatsApp é caso legítimo; consulta criada para esse paciente vai para
 *   status `sem-canal` imediatamente (fallback humano).
 * - `anonimizado_em` suporta LGPD (C-003); quando ≠ null, PII está
 *   sobrescrita.
 * - Unique parcial `(clinica_id, telefone_whatsapp)` com WHERE
 *   `telefone_whatsapp IS NOT NULL AND anonimizado_em IS NULL` permite:
 *     (1) múltiplos pacientes sem telefone;
 *     (2) múltiplos pacientes anonimizados (podem ter tido mesmo telefone no passado).
 *
 * Origem: FR-001 · FR-033 · C-003 · NFR-003 · P-03 · T-003.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clinica_id')
                ->constrained('clinicas')
                ->restrictOnDelete();

            // PII (NFR-003) — nomenclatura pii: para MascarararPiiProcessor capturar.
            $table->string('nome', 180);
            $table->string('telefone_whatsapp', 20)->nullable();
            $table->string('email', 180)->nullable();

            // C-003 tombstone.
            $table->timestampTz('anonimizado_em')->nullable();

            $table->timestamps();
        });

        // Unique parcial — Laravel não suporta partial index declarativo nativo,
        // então emitido via SQL. Suporte PG apenas.
        DB::statement(
            'CREATE UNIQUE INDEX pacientes_clinica_telefone_unique '.
            'ON pacientes (clinica_id, telefone_whatsapp) '.
            'WHERE telefone_whatsapp IS NOT NULL AND anonimizado_em IS NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS pacientes_clinica_telefone_unique');
        Schema::dropIfExists('pacientes');
    }
};
