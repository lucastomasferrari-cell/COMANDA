<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Color hex #RRGGBB. Nullable: las categorias existentes quedan sin
            // color y el frontend/model asigna automaticamente desde la paleta
            // al renderizar o al crear nuevas.
            $table->string('color', 7)->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
