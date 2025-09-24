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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Columnas para la relación polimórfica
            $table->morphs('permissible'); // Esto crea permissible_id (bigint unsigned) y permissible_type (string)

            $table->enum('permission_level', ['editor', 'visualizador']);
            $table->timestamps();

            // Asegurar que un usuario solo tenga un tipo de permiso por objeto
            $table->unique(['user_id', 'permissible_id', 'permissible_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
