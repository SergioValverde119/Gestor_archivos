<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cargo',
        'role',
        'area_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the area this user manages, if they are a 'jefe_area'.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get the oficios that this user has received.
     */
    public function oficiosRecibidos(): HasMany
    {
        return $this->hasMany(Oficio::class, 'recibido_por_user_id');
    }

    /**
     * Get all explicit permissions for the user.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}
