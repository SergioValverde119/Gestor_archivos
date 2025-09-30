<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FolioSequenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Establece el contador inicial para el folio de oficio de SALIDA
        DB::table('folio_sequences')->updateOrInsert(
            ['name' => 'salida'],
            ['last_number' => 2] // Empezará en 1
        );

        // Establece el contador inicial para el folio interno
        DB::table('folio_sequences')->updateOrInsert(
            ['name' => 'interno'],
            ['last_number' => 4013]  // Lo dejamos como lo tenías para pruebas
        );
    }
}



