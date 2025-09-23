<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'folio_oficio',
        'remitente',
        'destinatario',
        'asunto',
        'descripcion',
        'folio_interno',
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
     * The relationships that should always be loaded.
     *
     * @var array<int, string>
     */
    protected $with = [
        'expediente',
        'documentos',
        'recibidoPor',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_recepcion' => 'date',
            'fecha_limite' => 'date',
            'fecha_turno_dgaf' => 'date',
            'tiene_turno_dgaf' => 'boolean',
        ];
    }

    /**
     * El expediente al que pertenece este oficio.
     */
    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class);
    }

    /**
     * El usuario que recibió físicamente este oficio.
     */
    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por_user_id');
    }

    /**
     * El oficio original al que este oficio está respondiendo.
     */
    public function respuestaA(): BelongsTo
    {
        return $this->belongsTo(Oficio::class, 'oficio_respuesta_id');
    }
    
    /**
     * Los oficios que son respuesta a este oficio.
     */
    public function respuestas(): HasMany
    {
        return $this->hasMany(Oficio::class, 'oficio_respuesta_id');
    }

    /**
     * Todos los documentos (principal y anexos) asociados a este oficio.
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * Los permisos específicos de usuario para este oficio.
     */
    public function permissions(): MorphMany
    {
        return $this->morphMany(Permission::class, 'permissible');
    }

    /**
     * Un scope local para filtrar oficios vencidos.
     */
    public function scopeVencidos($query)
    {
        return $query->where('fecha_limite', '<', now())
                     ->where('status', '!=', 'Resuelto');
    }
}
