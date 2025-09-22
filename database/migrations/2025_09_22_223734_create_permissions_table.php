<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->morphs('permissible'); // Crea permissible_id y permissible_type
            $table->enum('permission_level', ['editor', 'visualizador']);
            $table->timestamps();
            $table->unique(['user_id', 'permissible_id', 'permissible_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
