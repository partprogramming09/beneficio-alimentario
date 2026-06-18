<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('institucion_estudiantes', function (Blueprint $table) {
            $table->string('documento', 20)->primary();
            $table->string('nombre_completo', 100);
            $table->string('grupo', 20);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institucion_estudiantes');
    }
};
