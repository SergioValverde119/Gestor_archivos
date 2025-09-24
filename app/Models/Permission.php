<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'permissible_id',
        'permissible_type',
        'permission_level',
    ];

    /**
     * Get the user that the permission belongs to.
     * Obtiene el usuario al que se le otorgó el permiso.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent permissible model (oficio or expediente).
     * Obtiene el modelo al que se le aplica el permiso (un Oficio o un Expediente).
     */
    public function permissible(): MorphTo
    {
        return $this->morphTo();
    }
}