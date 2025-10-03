<?php

namespace App\Http\Controllers\Users;

use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->with('area')
            // --- NUEVO: Lógica de búsqueda y filtros ---
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->input('role'), function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($request->input('area_id'), function ($query, $areaId) {
                $query->where('area_id', $areaId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString(); // Mantiene los filtros en la paginación

        return Inertia::render('Users/Index', [
            'users' => $users,
            'areas' => Area::all(['id', 'nombre']), // Envía la lista de áreas para el filtro
            'filters' => $request->only(['search', 'role', 'area_id']), // Envía los filtros actuales
        ]);
    }

    // ... El resto de los métodos (create, store, edit, etc.) no cambian ...
    public function create()
    {
        return Inertia::render('Users/Create', [
            'areas' => Area::all(['id', 'nombre']),
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'cargo' => 'nullable|string|max:255',
            'role' => ['required', Rule::in(['admin', 'director', 'jefe_area', 'operativo'])],
            'area_id' => 'required_if:role,jefe_area,operativo|nullable|exists:areas,id',
        ]);
        
        $validatedData['password'] = Hash::make($validatedData['password']);

        if (in_array($validatedData['role'], ['admin', 'director'])) {
            $validatedData['area_id'] = null;
        }

        User::create($validatedData);

        return redirect()->route('users.create')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user,
            'areas' => Area::all(['id', 'nombre']),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'cargo' => 'nullable|string|max:255',
            'role' => ['required', Rule::in(['admin', 'director', 'jefe_area', 'operativo'])],
            'area_id' => 'required_if:role,jefe_area,operativo|nullable|exists:areas,id',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $validatedData['password'] = Hash::make($request->password);
        }

        if (in_array($validatedData['role'], ['admin', 'director'])) {
            $validatedData['area_id'] = null;
        }

        $user->update($validatedData);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }
    
    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser->id === $user->id) {
            return redirect()->back()->with('error', 'No te puedes eliminar a ti mismo.');
        }
        
        if ($currentUser->role !== 'admin') {
            if ($user->permissions()->exists()) {
                return redirect()->back()->with('error', 'No se puede eliminar un usuario con permisos asignados.');
            }
        }

        if (in_array($user->role, ['admin', 'director'])) {
            $adminCount = User::whereIn('role', ['admin', 'director'])->count();
            if ($adminCount <= 1) {
                return redirect()->back()->with('error', 'No se puede eliminar al último administrador del sistema.');
            }
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}