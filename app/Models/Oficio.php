<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Oficio extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio_oficio', // <--- Se agregó esta línea
        'remitente', 
        'asunto', 
        'situacion', 
        'folio_interno', 
        'fecha_recepcion',
        'fecha_limite', 
        'prioridad_id', 
        'area_id', 
        'asignado_a_user_id', 
        'status',
    ];

    // Casting de atributos para manejar las fechas como objetos de Carbon
    protected $casts = [
        'fecha_recepcion' => 'date',
        'fecha_limite' => 'date',
    ];

    // Relaciones
    public function prioridad(): BelongsTo
    {
        return $this->belongsTo(Prioridad::class);
    }
    
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a_user_id');
    }

    // Un scope local para filtrar oficios vencidos
    public function scopeVencidos($query)
    {
        return $query->where('fecha_limite', '<', now())
                     ->where('status', '!=', 'Completado');
    }
}