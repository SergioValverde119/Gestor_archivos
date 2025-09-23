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
        Schema::table('users', function (Blueprint $table) {
            // Añade la columna 'role' para definir los permisos del sistema
            $table->enum('role', ['admin', 'director', 'jefe_area', 'operativo'])
                  ->default('operativo')
                  ->after('cargo');

            // Añade la columna 'area_id' para los jefes de área
            $table->foreignId('area_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('areas')
                  ->onDelete('set null'); // Si se borra el área, el usuario no se borra, solo se desvincula
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn(['role', 'area_id']);
        });
    }
};
