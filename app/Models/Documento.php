<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'oficio_id',
        'nombre_documento',
        'ruta_almacenamiento',
        'tipo_documento',
    ];

    /**
     * Get the oficio that owns the Documento.
     */
    public function oficio(): BelongsTo
    {
        return $this->belongsTo(Oficio::class);
    }
}