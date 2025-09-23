<?php

namespace App\Http\Controllers;

use App\Models\Oficio;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class DocumentoController extends Controller
{
    /**
     * Store a newly created document (anexo) for an existing oficio.
     * Almacena nuevos anexos para un oficio ya existente.
     */
    public function store(Request $request, Oficio $oficio)
    {
        // Opcional: Verificar si el usuario actual tiene permiso para editar el oficio
        // Gate::authorize('update', $oficio);

        $validated = $request->validate([
            'anexos' => 'required|array|min:1',
            'anexos.*' => 'file|mimes:pdf,doc,docx,jpg,png|max:2048',
        ]);

        foreach ($request->file('anexos') as $anexo) {
            $path = $anexo->store('documentos', 'public');
            $oficio->documentos()->create([
                'nombre_documento' => $anexo->getClientOriginalName(),
                'ruta_almacenamiento' => $path,
                'tipo_documento' => $anexo->getClientOriginalExtension(),
                'rol_documento' => 'anexo',
            ]);
        }

        return redirect()->back()->with('success', 'Anexos subidos correctamente.');
    }

    /**
     * Remove the specified document from storage.
     * Elimina un documento específico (principal o anexo).
     */
    public function destroy(Documento $documento)
    {
        // Opcional: Verificar si el usuario actual tiene permiso para editar el oficio al que pertenece el documento
        // Gate::authorize('update', $documento->oficio);

        // Prevenir la eliminación del documento principal si es el único que queda
        if ($documento->rol_documento === 'principal' && $documento->oficio->documentos()->count() <= 1) {
            return redirect()->back()->with('error', 'No se puede eliminar el documento principal si es el único archivo del oficio.');
        }

        // Eliminar el archivo físico
        Storage::disk('public')->delete($documento->ruta_almacenamiento);

        // Eliminar el registro de la base de datos
        $documento->delete();

        return redirect()->back()->with('success', 'Documento eliminado correctamente.');
    }
}
