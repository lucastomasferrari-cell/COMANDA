<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // MySQL no soporta partial unique indexes (WHERE) como Postgres.
        // Workaround: generated column que devuelve la clave de unicidad solo
        // cuando status='open', y NULL en los demas casos. MySQL trata
        // multiples NULL como distintos, asi que sesiones cerradas conviven.
        DB::statement(<<<'SQL'
            ALTER TABLE pos_sessions
                ADD COLUMN open_uniqueness_key VARCHAR(32)
                GENERATED ALWAYS AS (
                    CASE WHEN status = 'open'
                        THEN CONCAT(branch_id, '-', pos_register_id)
                    END
                ) VIRTUAL,
                ADD UNIQUE KEY uniq_open_pos_session (open_uniqueness_key)
        SQL);
    }

    public function down(): void
    {
        Schema::table('pos_sessions', function ($table) {
            $table->dropUnique('uniq_open_pos_session');
            $table->dropColumn('open_uniqueness_key');
        });
    }
};
