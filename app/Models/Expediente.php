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
     * Todos los oficios que pertenecen a este expediente.
     */
    public function oficios(): HasMany
    {
        return $this->hasMany(Oficio::class);
    }

    /**
     * Las áreas a las que pertenece este expediente.
     */
    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'area_expediente');
    }

    /**
     * Los permisos específicos de usuario para este expediente.
     */
    public function permissions(): MorphMany
    {
        return $this->morphMany(Permission::class, 'permissible');
    }
}
