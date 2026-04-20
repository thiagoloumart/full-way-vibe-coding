<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cria tabela `consultas`.
 *
 * - `status_cache` é **projeção** atualizada por listener (DT-02).
 * - `janela_*_horas_usada` são **snapshot** da config da Clínica no
 *   momento da criação (FR-029 — alteração posterior da config não afeta).
 * - `datahora_agendada` em `timestamptz` (UTC); conversão BRT só na UI.
 *
 * Origem: FR-003..FR-029 · C-004 · DT-02.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clinica_id')->constrained('clinicas')->restrictOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('medico_id')->constrained('medicos')->restrictOnDelete();

            $table->timestampTz('datahora_agendada');

            // Projeção DT-02. Nullable até F3 ativar listener.
            $table->string('status_cache', 32)->nullable();

            $table->foreignId('criado_por_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Snapshot FR-029.
            $table->smallInteger('janela_lembrete_horas_usada');
            $table->smallInteger('janela_silencio_horas_usada');

            $table->timestamps();

            $table->index(['clinica_id', 'datahora_agendada']);
            $table->index(['status_cache']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};
