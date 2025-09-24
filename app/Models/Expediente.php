<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Expediente extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'numero_expediente',
        'titulo',
        'descripcion',
    ];

    /**
     * Get all the oficios for the Expediente.
     * Obtiene todos los oficios que pertenecen a este expediente.
     */
    public function oficios(): HasMany
    {
        return $this->hasMany(Oficio::class);
    }

    /**
     * The areas that belong to the Expediente.
     * Las áreas que tienen permiso sobre este expediente.
     */
    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'area_expediente');
    }

    /**
     * Get all the expediente's permissions.
     * Obtiene todos los permisos explícitos sobre este expediente.
     */
    public function permissions(): MorphMany
    {
        return $this->morphMany(Permission::class, 'permissible');
    }
}