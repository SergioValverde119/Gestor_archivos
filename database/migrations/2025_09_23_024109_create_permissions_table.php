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
        Schema::create('area_expediente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->foreignId('expediente_id')->constrained('expedientes')->onDelete('cascade');
            $table->timestamps();

            // Clave única para evitar duplicados (un área no puede estar dos veces en el mismo expediente)
            $table->unique(['area_id', 'expediente_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('area_expediente');
    }
};