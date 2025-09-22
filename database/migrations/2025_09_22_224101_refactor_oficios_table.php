<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oficios', function (Blueprint $table) {
            // Eliminar columnas antiguas y sus índices
            $table->dropForeign(['prioridad_id']);
            $table->dropForeign(['area_id']);
            $table->dropForeign(['asignado_a_user_id']);
            $table->dropColumn(['prioridad_id', 'area_id', 'asignado_a_user_id']);

            // Renombrar 'situacion' a 'descripcion'
            $table->renameColumn('situacion', 'descripcion');

            // Añadir nuevas columnas
            $table->foreignId('expediente_id')->nullable()->after('id')->constrained('expedientes')->onDelete('cascade');
            $table->enum('tipo', ['entrada', 'salida'])->default('entrada')->after('expediente_id');
            $table->string('destinatario')->nullable()->after('remitente');
            $table->enum('prioridad', ['Ordinario', 'Urgente', 'Extremadamente Urgente'])->nullable()->after('fecha_limite');
            $table->foreignId('recibido_por_user_id')->nullable()->after('prioridad')->constrained('users')->onDelete('set null');
            $table->foreignId('oficio_respuesta_id')->nullable()->after('recibido_por_user_id')->constrained('oficios')->onDelete('set null');
            $table->text('resolucion')->nullable()->after('status');
            $table->boolean('tiene_turno_dgaf')->default(false)->after('resolucion');
            $table->string('folio_turno_dgaf')->nullable()->after('tiene_turno_dgaf');
            $table->date('fecha_turno_dgaf')->nullable()->after('folio_turno_dgaf');
        });
    }

    public function down(): void
    {
        // Revertir todo en el orden inverso
        Schema::table('oficios', function (Blueprint $table) {
            $table->dropForeign(['expediente_id']);
            $table->dropForeign(['recibido_por_user_id']);
            $table->dropForeign(['oficio_respuesta_id']);
            $table->dropColumn([
                'expediente_id', 'tipo', 'destinatario', 'prioridad', 'recibido_por_user_id',
                'oficio_respuesta_id', 'resolucion', 'tiene_turno_dgaf', 'folio_turno_dgaf', 'fecha_turno_dgaf'
            ]);

            $table->renameColumn('descripcion', 'situacion');

            $table->foreignId('prioridad_id')->nullable()->constrained('prioridades');
            $table->foreignId('area_id')->nullable()->constrained('areas');
            $table->foreignId('asignado_a_user_id')->nullable()->constrained('users');
        });
    }
};