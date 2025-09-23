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
        // Se eliminan las tablas que ya no son necesarias en la nueva arquitectura
        Schema::dropIfExists('prioridades');
        Schema::dropIfExists('turnos_dgaf');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Se recrean las tablas si se revierte la migración (opcional, pero buena práctica)
        Schema::create('prioridades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('turnos_dgaf', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oficio_id')->unique()->constrained('oficios');
            $table->string('folio_id')->unique();
            $table->string('folio_dgaf');
            $table->string('remitente_turno');
            $table->string('puesto_remitente');
            $table->date('fecha_turno');
            $table->timestamps();
        });
    }
};