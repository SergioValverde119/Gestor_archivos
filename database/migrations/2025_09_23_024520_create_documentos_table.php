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
        // Se crea la tabla 'documentos' con su estructura final y correcta
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oficio_id')->constrained('oficios')->onDelete('cascade');
            $table->string('nombre_documento');
            $table->string('ruta_almacenamiento');
            $table->string('tipo_documento');
            $table->enum('rol_documento', ['principal', 'anexo'])->default('principal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};