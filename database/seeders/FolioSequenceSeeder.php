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
        // Usamos updateOrInsert para que puedas correr este seeder varias veces
        // sin que se dupliquen los registros. Si ya existen, solo los actualiza.

        // Establece el contador inicial para el folio de oficio
        DB::table('folio_sequences')->updateOrInsert(
            ['name' => 'oficio'],
            ['last_number' => 1200]
        );

        // Establece el contador inicial para el folio interno
        DB::table('folio_sequences')->updateOrInsert(
            ['name' => 'interno'],
            ['last_number' => 4000]
        );
    }
}
