<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Oficio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
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
     * Get the prioridad that owns the oficio.
     */
    public function prioridad(): BelongsTo
    {
        return $this->belongsTo(Prioridad::class);
    }

    /**
     * Get the area that ownsd the oficio.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
        * Get the user to whom the oficio is assigned.
     */
    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a_user_id');
    }
}