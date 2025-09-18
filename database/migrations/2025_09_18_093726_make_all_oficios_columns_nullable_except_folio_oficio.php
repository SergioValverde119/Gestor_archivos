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
        Schema::table('oficios', function (Blueprint $table) {
            $table->string('remitente')->nullable()->change();
            $table->text('asunto')->nullable()->change();
            $table->text('situacion')->nullable()->change();
            $table->string('folio_interno')->nullable()->change();
            $table->date('fecha_recepcion')->nullable()->change();
            $table->date('fecha_limite')->nullable()->change();
            $table->foreignId('prioridad_id')->nullable()->change();
            $table->foreignId('area_id')->nullable()->change();
            $table->foreignId('asignado_a_user_id')->nullable()->change();
            $table->string('status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oficios', function (Blueprint $table) {
            $table->string('remitente')->nullable(false)->change();
            $table->text('asunto')->nullable(false)->change();
            $table->text('situacion')->nullable(false)->change();
            $table->string('folio_interno')->nullable(false)->change();
            $table->date('fecha_recepcion')->nullable(false)->change();
            $table->date('fecha_limite')->nullable(false)->change();
            $table->foreignId('prioridad_id')->nullable(false)->change();
            $table->foreignId('area_id')->nullable(false)->change();
            $table->foreignId('asignado_a_user_id')->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
        });
    }
};