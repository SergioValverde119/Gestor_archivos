<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
    ];

    /**
     * Get the users that are heads of this area.
     * Obtiene los usuarios que son jefes de esta área.
     */
    public function jefes(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * The expedientes that belong to the area.
     * Los expedientes que pertenecen a esta área.
     */
    public function expedientes(): BelongsToMany
    {
        return $this->belongsToMany(Expediente::class, 'area_expediente');
    }
}