<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('area')->latest()->paginate(10);

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Users/Create', [
            'areas' => Area::all(['id', 'nombre']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'cargo' => 'nullable|string|max:255',
            'role' => ['required', Rule::in(['admin', 'director', 'jefe_area', 'operativo'])],
            // --- CORRECCIÓN: El área solo es obligatoria para ciertos roles ---
            'area_id' => 'required_if:role,jefe_area,operativo|nullable|exists:areas,id',
        ]);
        
        $validatedData['password'] = Hash::make($validatedData['password']);

        // --- CORRECCIÓN: Asegurarse de que area_id sea null para roles de alto nivel ---
        if (in_array($validatedData['role'], ['admin', 'director'])) {
            $validatedData['area_id'] = null;
        }

        User::create($validatedData);

        return redirect()->route('users.create')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('area');

        return Inertia::render('Users/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user,
            'areas' => Area::all(['id', 'nombre']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'cargo' => 'nullable|string|max:255',
            'role' => ['required', Rule::in(['admin', 'director', 'jefe_area', 'operativo'])],
             // --- CORRECCIÓN: El área solo es obligatoria para ciertos roles ---
            'area_id' => 'required_if:role,jefe_area,operativo|nullable|exists:areas,id',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $validatedData['password'] = Hash::make($request->password);
        }

        // --- CORRECCIÓN: Asegurarse de que area_id sea null para roles de alto nivel ---
        if (in_array($validatedData['role'], ['admin', 'director'])) {
            $validatedData['area_id'] = null;
        }

        $user->update($validatedData);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->permissions()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar un usuario con permisos asignados.');
        }
        
        if (in_array($user->role, ['admin', 'director'])) {
            $adminCount = User::whereIn('role', ['admin', 'director'])->count();
            if ($adminCount <= 1) {
                return redirect()->back()->with('error', 'No se puede eliminar al último administrador.');
            }
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}