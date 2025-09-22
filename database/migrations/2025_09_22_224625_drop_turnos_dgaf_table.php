<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('turnos_dgaf');
    }

    public function down(): void
    {
        Schema::create('turnos_dgaf', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oficio_id')->constrained('oficios');
            $table->string('folio_id')->unique();
            $table->string('folio_dgaf');
            $table->string('remitente_turno');
            $table->string('puesto_remitente');
            $table->date('fecha_turno');
            $table->timestamps();
        });
    }
};
