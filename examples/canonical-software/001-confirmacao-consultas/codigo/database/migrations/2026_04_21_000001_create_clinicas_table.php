<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cria tabela `clinicas` com defaults C-004.
 *
 * MVP: 1 linha (single-tenant D-003), criada pelo `ClinicaSeeder`.
 *
 * Origem: FR-028 · C-002 · C-004 · D-003 · T-001.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinicas', function (Blueprint $table): void {
            $table->id();
            $table->string('nome', 120);

            // C-004 defaults (configuráveis pela UI).
            $table->smallInteger('janela_lembrete_horas')->default(24);
            $table->smallInteger('janela_silencio_horas')->default(4);
            $table->smallInteger('envio_inicio_hora')->default(8);
            $table->smallInteger('envio_fim_hora')->default(20);
            $table->smallInteger('retry_max')->default(3);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinicas');
    }
};
