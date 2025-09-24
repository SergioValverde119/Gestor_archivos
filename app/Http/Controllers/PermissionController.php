<?php

namespace App\Http\Controllers;

use App\Models\Oficio;
use App\Models\Expediente;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class PermissionController extends Controller
{
    /**
     * Store a newly created permission in storage.
     * Asigna un permiso a un usuario sobre un oficio o un expediente.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission_level' => ['required', Rule::in(['editor', 'visualizador'])],
            'permissible_id' => 'required|integer',
            'permissible_type' => ['required', Rule::in(['oficio', 'expediente'])],
        ]);

        $modelClass = $validated['permissible_type'] === 'oficio' ? Oficio::class : Expediente::class;
        $permissible = $modelClass::findOrFail($validated['permissible_id']);

        // Opcional: Verificar si el usuario actual tiene permiso para dar permisos
        // Gate::authorize('share', $permissible);

        // Crear el permiso usando la relación polimórfica
        $permissible->permissions()->create([
            'user_id' => $validated['user_id'],
            'permission_level' => $validated['permission_level'],
        ]);

        return redirect()->back()->with('success', 'Permiso asignado correctamente.');
    }

    /**
     * Remove the specified permission from storage.
     * Revoca un permiso específico.
     */
    public function destroy(Permission $permission)
    {
        // Opcional: Verificar si el usuario actual tiene permiso para revocar este permiso
        // Gate::authorize('unshare', $permission->permissible);
        
        $permission->delete();

        return redirect()->back()->with('success', 'Permiso revocado correctamente.');
    }
}