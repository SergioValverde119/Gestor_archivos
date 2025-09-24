
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
        // Se crea la tabla con las columnas necesarias, pero sin las relaciones
        Schema::create('area_expediente', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('area_id');
            $table->unsignedBigInteger('expediente_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_expediente');
    }
};
