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
        Schema::create('oficios', function (Blueprint $table) {
            $table->id();
            $table->string('remitente');
            $table->text('asunto');
            $table->text('situacion');
            $table->string('folio_interno')->unique();
            $table->date('fecha_recepcion');
            $table->date('fecha_limite')->nullable();
            
            $table->foreignId('prioridad_id')->constrained('prioridades');
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('asignado_a_user_id')->constrained('users');

            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * 
     */
    public function down(): void
    {
        Schema::dropIfExists('oficios');
    }
};
