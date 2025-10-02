<?php

namespace App\Policies;

use App\Models\Oficio;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OficioPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * Define quién puede ver la lista de oficios.
     */
    public function viewAny(User $user): bool
    {
        // Cualquier usuario autenticado puede, en principio, ver la lista.
        // El controlador se encargará de filtrar qué registros ve cada uno.
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Define quién puede ver el detalle de un oficio específico.
     */
    public function view(User $user, Oficio $oficio): bool
    {
        // 1. Admins y directores pueden ver todo.
        if (in_array($user->role, ['admin', 'director'])) {
            return true;
        }

        // 2. Jefes de área pueden ver oficios de su área.
        if ($user->role === 'jefe_area' && $user->area_id) {
            // Comprueba si alguna de las áreas del expediente coincide con la del jefe.
            return $oficio->expediente->areas->contains('id', $user->area_id);
        }

        // 3. Operativos pueden ver oficios si tienen permiso explícito
        // sobre el oficio o sobre el expediente completo.
        $hasPermission = $user->permissions()
            ->where(function ($query) use ($oficio) {
                $query->where(fn ($q) => $q->where('permissible_type', Oficio::class)->where('permissible_id', $oficio->id))
                      ->orWhere(fn ($q) => $q->where('permissible_type', 'App\Models\Expediente')->where('permissible_id', $oficio->expediente_id));
            })->exists();

        return $hasPermission;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Todos los usuarios autenticados pueden crear oficios.
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Oficio $oficio): bool
    {
        // Solo admins, directores, o usuarios con permiso de 'editor' pueden actualizar.
        if (in_array($user->role, ['admin', 'director'])) {
            return true;
        }
        
        $hasEditPermission = $user->permissions()
            ->where('permission_level', 'editor')
            ->where(function ($query) use ($oficio) {
                $query->where(fn ($q) => $q->where('permissible_type', Oficio::class)->where('permissible_id', $oficio->id))
                      ->orWhere(fn ($q) => $q->where('permissible_type', 'App\Models\Expediente')->where('permissible_id', $oficio->expediente_id));
            })->exists();

        return $hasEditPermission;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Oficio $oficio): bool
    {
        // Solo admins y directores pueden borrar.
        return in_array($user->role, ['admin', 'director']);
    }
}
