<?php

namespace App\Services;

use App\Models\Oficio;
use App\Models\Expediente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OficioService
{
    /**
     * Crea un nuevo oficio de entrada con su expediente y documentos.
     */
    public function crearOficioEntrada(array $validated, Request $request): Oficio
    {
        return DB::transaction(function () use ($validated, $request) {
            
            $folioInterno = $this->getNextFolio('interno', true);
            
            if (!empty($validated['oficio_respuesta_id'])) {
                $oficioOriginal = Oficio::findOrFail($validated['oficio_respuesta_id']);
                $expediente = $oficioOriginal->expediente;

                if (!empty($validated['area_ids'])) {
                    $expediente->areas()->syncWithoutDetaching($validated['area_ids']);
                }
            } else {
                $expediente = Expediente::create([
                    'numero_expediente' => $folioInterno,
                    'titulo' => $validated['asunto'] ?? 'Oficio de Entrada sin Asunto',
                ]);
                $expediente->areas()->sync($validated['area_ids']);
            }
            
            $oficioData = array_merge($validated, [
                'folio_interno' => $folioInterno,
                'tipo' => 'entrada',
            ]);

            $oficio = $expediente->oficios()->create($oficioData);

            $this->procesarDocumentos($request, $oficio);
            $this->procesarAsignaciones($validated, $oficio);
            
            DB::table('folio_sequences')->where('name', 'interno')->increment('last_number');
            
            return $oficio;
        });
    }

    /**
     * Crea un nuevo oficio de salida con su expediente y documentos.
     */
    public function crearOficioSalida(array $validated, Request $request): Oficio
    {
        return DB::transaction(function () use ($validated, $request) {
            
            $folioSalida = $this->getNextFolio('salida', true);
            
            if (!empty($validated['oficio_respuesta_id'])) {
                $oficioOriginal = Oficio::findOrFail($validated['oficio_respuesta_id']);
                $expediente = $oficioOriginal->expediente;
            } else {
                $expediente = Expediente::create([
                    'numero_expediente' => $folioSalida,
                    'titulo' => $validated['asunto'] ?? 'Oficio de Salida sin Asunto',
                ]);
                $expediente->areas()->sync($validated['area_ids'] ?? []);
            }

            $oficioData = array_merge($validated, [
                'tipo' => 'salida',
                'folio_salida' => $folioSalida,
                'folio_interno' => null,
            ]);

            $oficio = $expediente->oficios()->create($oficioData);

            $this->procesarDocumentos($request, $oficio);
            $this->procesarAsignaciones($validated, $oficio, true); // true para modo 'create'
            
            DB::table('folio_sequences')->where('name', 'salida')->increment('last_number');

            return $oficio;
        });
    }

    /**
     * Sube los documentos principal y anexos de un oficio.
     */
    private function procesarDocumentos(Request $request, Oficio $oficio): void
    {
        if ($request->hasFile('documento_principal')) {
            $file = $request->file('documento_principal');
            $path = $file->store('documentos', 'public');
            $oficio->documentos()->create([
                'nombre_documento' => $file->getClientOriginalName(),
                'ruta_almacenamiento' => $path,
                'tipo_documento' => $file->getClientOriginalExtension(),
                'rol_documento' => 'principal',
            ]);
        }
        if ($request->hasFile('anexos')) {
            foreach ($request->file('anexos') as $anexo) {
                $pathAnexo = $anexo->store('documentos', 'public');
                $oficio->documentos()->create([
                    'nombre_documento' => $anexo->getClientOriginalName(),
                    'ruta_almacenamiento' => $pathAnexo,
                    'tipo_documento' => $anexo->getClientOriginalExtension(),
                    'rol_documento' => 'anexo',
                ]);
            }
        }
    }

    /**
     * Guarda las asignaciones de permisos de un oficio.
     */
    private function procesarAsignaciones(array $validated, Oficio $oficio, bool $useCreate = false): void
    {
        if (empty($validated['asignaciones'])) {
            return;
        }

        foreach ($validated['asignaciones'] as $asignacion) {
            if (empty($asignacion['user_id'])) {
                continue;
            }
            
            if ($useCreate) {
                 $oficio->permissions()->create([
                    'user_id' => $asignacion['user_id'],
                    'permission_level' => $asignacion['permission'],
                ]);
            } else {
                $oficio->permissions()->updateOrCreate(
                    ['user_id' => $asignacion['user_id']],
                    ['permission_level' => $asignacion['permission']]
                );
            }
        }
    }

    /**
     * Helper para obtener el siguiente número de folio.
     * Lo hacemos público para que los controladores lo usen en 'create'.
     */
    public function getNextFolio(string $name, bool $lockForUpdate = false): string
    {
        $query = DB::table('folio_sequences')->where('name', $name);
        if ($lockForUpdate) {
            $query->lockForUpdate();
        }
        $sequence = $query->first();
        
        if (!$sequence) {
            return '0001';
        }
        
        return str_pad($sequence->last_number + 1, 4, '0', STR_PAD_LEFT);
    }
}