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
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->string('documento', 20)->primary();
            $table->string('nombres', 50);
            $table->string('apellidos', 50);
            $table->string('grupo', 20);
            $table->enum('estado', ['Pendiente', 'Activo', 'Suspendido', 'Inactivo'])->default('Pendiente');
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('documento')
                  ->references('documento')
                  ->on('institucion_estudiantes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
