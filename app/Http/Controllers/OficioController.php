<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Oficio extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oficios';

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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'fecha_recepcion' => 'date',
        'fecha_limite' => 'date',
    ];

    /**
     * Get the prioridad that owns the oficio.
     */
    public function prioridad(): BelongsTo
    {
        return $this->belongsTo(Prioridad::class);
    }

    /**
     * Get the area that owns the oficio.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get the user that owns the oficio.
     */
    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a_user_id');
    }
}
