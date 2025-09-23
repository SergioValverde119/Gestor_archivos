<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Esta tabla actuará como el "cerebro" central para todos los contadores de folios.
        // Guardará el último número utilizado para cada tipo de folio.
        Schema::create('folio_sequences', function (Blueprint $table) {
            // El 'name' identifica el tipo de contador (ej. 'oficio', 'interno').
            // Lo hacemos clave primaria para asegurar que no haya contadores duplicados.
            $table->string('name')->primary();
            
            // 'last_number' almacena el último número consecutivo que se utilizó.
            $table->unsignedInteger('last_number')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('folio_sequences');
    }
};
