<?php

namespace App\Http\Controllers\Documentos;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\Oficio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DocumentoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Descarga de forma segura un documento, verificando los permisos del usuario.
     */
    public function download(Documento $documento)
    {
        // 1. Se verifica si el usuario tiene permiso para ver el oficio al que pertenece este documento.
        $this->authorize('view', $documento->oficio);

        // 2. Se comprueba que el archivo exista en el disco público.
        if (!Storage::disk('public')->exists($documento->ruta_almacenamiento)) {
            abort(404, 'El archivo solicitado no fue encontrado en el disco.');
        }

        // --- CORRECCIÓN: Se utiliza el helper response()->download() para mayor compatibilidad ---
        // 3. Se construye la ruta completa al archivo en el servidor.
        $path = storage_path('app/public/' . $documento->ruta_almacenamiento);

        // 4. Se entrega el archivo para su descarga.
        return response()->download($path, $documento->nombre_documento);
    }

    /**
     * Almacena nuevos anexos para un oficio ya existente.
     */
    public function store(Request $request, Oficio $oficio)
    {
        $this->authorize('update', $oficio);

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
     * Elimina un documento específico (principal o anexo).
     */
    public function destroy(Documento $documento)
    {
        $this->authorize('update', $documento->oficio);

        if ($documento->rol_documento === 'principal' && $documento->oficio->documentos()->count() <= 1) {
            return redirect()->back()->with('error', 'No se puede eliminar el documento principal si es el único archivo del oficio.');
        }

        Storage::disk('public')->delete($documento->ruta_almacenamiento);
        $documento->delete();

        return redirect()->back()->with('success', 'Documento eliminado correctamente.');
    }
}
