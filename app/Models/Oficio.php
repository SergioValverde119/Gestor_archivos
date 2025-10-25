<?php

namespace App\Models;

use App\Models\Concerns\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Oficio extends Model
{
    use HasFactory, Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'expediente_id', 'tipo', 'folio_externo', 'folio_salida', 'folio_interno',
        'remitente', 'destinatario', 'asunto', 'descripcion', 'fecha_recepcion',
        'fecha_limite', 'prioridad', 'recibido_por_user_id', 'oficio_respuesta_id',
        'status', 'resolucion', 'tiene_turno_dgaf', 'folio_turno_dgaf', 'fecha_turno_dgaf',
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
     * Get the expediente that the oficio belongs to.
     */
    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class);
    }

    /**
     * Get the user who received the oficio.
     */
    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por_user_id');
    }
    

    /**
     * Get the oficio that this oficio is a response to.
     */
    public function respuestaA(): BelongsTo
    {
        return $this->belongsTo(Oficio::class, 'oficio_respuesta_id');
    }

    /**
     * Get all of the documents for the Oficio.
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * Get all of the oficio's permissions.
     */
    public function permissions(): MorphMany
    {
        return $this->morphMany(Permission::class, 'permissible');
    }

    /**
     * Accessor para obtener solo el documento principal.
     * Busca en la colección de 'documentos' ya cargada.
     */
    protected function documentoPrincipal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->documentos->firstWhere('rol_documento', 'principal'),
        );
    }

    /**
     * Accessor para obtener solo los documentos anexos.
     * Busca en la colección de 'documentos' ya cargada.
     */
    protected function anexos(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->documentos->where('rol_documento', 'anexo'),
        );
    }
}