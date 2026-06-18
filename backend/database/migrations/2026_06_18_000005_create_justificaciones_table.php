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
        Schema::create('justificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('documento', 20);
            $table->date('fecha_inasistencia');
            $table->text('motivo');
            $table->enum('estado', ['Pendiente', 'Aprobado', 'Rechazado'])->default('Pendiente');
            $table->timestamp('creado_en')->useCurrent();

            $table->foreign('documento')
                  ->references('documento')
                  ->on('estudiantes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('justificaciones');
    }
};
