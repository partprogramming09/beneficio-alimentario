<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstitucionEstudiantesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estudiantes = [
            ['documento' => '1001', 'nombre_completo' => 'Juan Pérez', 'grupo' => '10-A'],
            ['documento' => '1002', 'nombre_completo' => 'Maria Gomez', 'grupo' => '10-A'],
            ['documento' => '1003', 'nombre_completo' => 'Carlos Ruiz', 'grupo' => '10-B'],
            ['documento' => '1004', 'nombre_completo' => 'Ana Pineda', 'grupo' => '10-B'],
            ['documento' => '1005', 'nombre_completo' => 'Luis Ortiz', 'grupo' => '11-A'],
            ['documento' => '1006', 'nombre_completo' => 'Diana Silva', 'grupo' => '11-A'],
            ['documento' => '1007', 'nombre_completo' => 'Jorge Rios', 'grupo' => '11-B'],
            ['documento' => '1008', 'nombre_completo' => 'Laura Castro', 'grupo' => '11-B'],
            ['documento' => '1009', 'nombre_completo' => 'Andres Villa', 'grupo' => '10-A'],
            ['documento' => '1010', 'nombre_completo' => 'Sofia Rojas', 'grupo' => '11-A'],
        ];

        DB::table('institucion_estudiantes')->insert($estudiantes);
    }
}
