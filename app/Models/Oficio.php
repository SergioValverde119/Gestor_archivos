<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Oficio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'expediente_id',
        'tipo',
        'folio_externo',
        'folio_salida',
        'folio_interno',
        'remitente',
        'destinatario',
        'asunto',
        'descripcion',
        'fecha_recepcion',
        'fecha_limite',
        'prioridad',
        'recibido_por_user_id',
        'oficio_respuesta_id',
        'status',
        'resolucion',
        'tiene_turno_dgaf',
        'folio_turno_dgaf',
        'fecha_turno_dgaf',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_recepcion' => 'date',
        'fecha_limite' => 'date',
        'fecha_turno_dgaf' => 'date',
        'tiene_turno_dgaf' => 'boolean',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array<int, string>
     */
    protected $with = ['expediente', 'documentoPrincipal'];

    /**
     * Get the expediente that the oficio belongs to.
     * Obtiene el expediente al que pertenece el oficio.
     */
    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class);
    }

    /**
     * Get the user who received the oficio.
     * Obtiene el usuario que recibió el oficio.
     */
    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por_user_id');
    }

    /**
     * Get the oficio that this oficio is a response to.
     * Obtiene el oficio al que este oficio responde.
     */
    public function respuestaA(): BelongsTo
    {
        return $this->belongsTo(Oficio::class, 'oficio_respuesta_id');
    }

    /**
     * Get all the documents for the Oficio.
     * Obtiene todos los documentos (principal y anexos) del oficio.
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * Get the main document for the Oficio.
     * Obtiene solo el documento principal del oficio.
     */
    public function documentoPrincipal(): HasOne
    {
        return $this->hasOne(Documento::class)->where('rol_documento', 'principal');
    }

    /**
     * Get all of the oficio's permissions.
     * Obtiene todos los permisos explícitos sobre este oficio.
     */
    public function permissions(): MorphMany
    {
        return $this->morphMany(Permission::class, 'permissible');
    }
}