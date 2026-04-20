<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Trigger PostgreSQL `BEFORE UPDATE OR DELETE ON eventos_consulta`.
 *
 * Segunda barreira (defense-in-depth DT-03) contra qualquer tentativa de
 * violar D-E-03 da constituição — inclusive comandos SQL crus que escapem
 * do Eloquent. A primeira barreira é App\Models\EventoConsulta (overrides).
 *
 * Benchmark de performance do trigger está planejado em T-059. R-02 do plan
 * documenta risco e threshold de degradação aceitável (≤ 20% vs baseline).
 *
 * Origem: DT-03 · D-E-03 · R-02.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION eventos_consulta_append_only()
            RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION
                    'eventos_consulta: append-only (D-E-03). % é proibido. '
                    'Correções usam INSERT de evento `correcao`; deleção de '
                    'PII usa AnonimizarPaciente (C-003).',
                    TG_OP;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_eventos_consulta_append_only
            BEFORE UPDATE OR DELETE ON eventos_consulta
            FOR EACH ROW
            EXECUTE FUNCTION eventos_consulta_append_only();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS trg_eventos_consulta_append_only ON eventos_consulta;
            DROP FUNCTION IF EXISTS eventos_consulta_append_only();
        SQL);
    }
};
