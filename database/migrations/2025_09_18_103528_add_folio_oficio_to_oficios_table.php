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
            // Asegúrate de que el folio del oficio sea una cadena única
            $table->string('folio_oficio')->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oficios', function (Blueprint $table) {
            $table->dropColumn('folio_oficio');
        });
    }
};