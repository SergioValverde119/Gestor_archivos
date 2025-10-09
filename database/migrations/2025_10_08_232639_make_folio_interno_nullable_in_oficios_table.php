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
            // Hacemos que la columna 'folio_interno' pueda ser nula y la hacemos no única
            $table->string('folio_interno')->nullable()->unique(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oficios', function (Blueprint $table) {
            // Este método revierte el cambio si es necesario
            $table->string('folio_interno')->nullable(false)->unique()->change();
        });
    }
};
