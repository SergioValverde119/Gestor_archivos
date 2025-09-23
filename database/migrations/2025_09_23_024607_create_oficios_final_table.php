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
        // Se crea la tabla 'oficios' con su estructura final y correcta
        Schema::create('oficios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->nullable()->constrained('expedientes')->onDelete('cascade');
            
            $table->enum('tipo', ['entrada', 'salida'])->default('entrada');
            $table->string('folio_externo')->unique()->nullable();
            $table->string('folio_salida')->unique()->nullable();
            $table->string('folio_interno')->unique()->nullable();
            
            $table->string('remitente')->nullable();
            $table->string('destinatario')->nullable();
            $table->text('asunto')->nullable();
            $table->text('descripcion')->nullable();
            
            $table->date('fecha_recepcion')->nullable();
            $table->date('fecha_limite')->nullable();
            
            $table->enum('prioridad', ['Ordinario', 'Urgente', 'Extremadamente Urgente'])->nullable();
            
            $table->foreignId('recibido_por_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('oficio_respuesta_id')->nullable()->constrained('oficios')->onDelete('set null');
            
            $table->string('status')->nullable();
            $table->text('resolucion')->nullable();
            
            $table->boolean('tiene_turno_dgaf')->default(false);
            $table->string('folio_turno_dgaf')->nullable();
            $table->date('fecha_turno_dgaf')->nullable();
            
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
        Schema::dropIfExists('oficios');
    }
};