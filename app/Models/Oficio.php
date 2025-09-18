<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Oficio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'folio_oficio',
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

    /**
     * The relationships that should always be loaded.
     *
     * @var array<string>
     */
    protected $with = ['prioridad', 'area', 'asignadoA', 'documento'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_recepcion' => 'date',
        'fecha_limite' => 'date',
    ];

    /**
     * Get the prioridad that owns the Oficio.
     */
    public function prioridad(): BelongsTo
    {
        return $this->belongsTo(Prioridad::class);
    }

    /**
     * Get the area that the Oficio belongs to.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get the user that the Oficio is assigned to.
     */
    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a_user_id');
    }

    /**
     * Get the documento record associated with the Oficio.
     */
    public function documento(): HasOne
    {
        return $this->hasOne(Documento::class);
    }

    /**
     * Un scope local para filtrar oficios vencidos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVencidos($query)
    {
        return $query->where('fecha_limite', '<', now())
                     ->where('status', '!=', 'Completado');
    }
}