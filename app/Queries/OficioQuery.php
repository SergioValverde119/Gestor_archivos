<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;

class OficioQuery extends Query
{
    /**
     * Filtra por el término de búsqueda general.
     */
    public function search(string $search): Builder
    {
        return $this->query->where(function ($q) use ($search) {
            $q->where('folio_externo', 'like', "%{$search}%")
              ->orWhere('folio_salida', 'like', "%{$search}%")
              ->orWhere('asunto', 'like', "%{$search}%");
        });
    }

    /**
     * Filtra por el tipo de oficio.
     */
    public function tipo(string $tipo): Builder
    {
        return $this->query->where('tipo', $tipo);
    }

    /**
     * Filtra si tiene o no turno DGAF.
     */
    public function tiene_turno_dgaf(string $value): Builder
    {
        return $this->query->where('tiene_turno_dgaf', $value === 'true');
    }

    /**
     * Filtra por áreas de expediente.
     */
    public function area_ids(array $areaIds): Builder
    {
        return $this->query->whereHas('expediente.areas', function ($q) use ($areaIds) {
            $q->whereIn('areas.id', $areaIds);
        });
    }

    /**
     * Filtros por rango de fecha de registro.
     */
    public function date_from(string $date): Builder
    {
        return $this->query->whereNotNull('created_at')->whereDate('created_at', '>=', $date);
    }
    public function date_to(string $date): Builder
    {
        return $this->query->whereNotNull('created_at')->whereDate('created_at', '<=', $date);
    }

    /**
     * Filtros por rango de fecha de recepción.
     */
    public function recepcion_from(string $date): Builder
    {
        return $this->query->whereNotNull('fecha_recepcion')->whereDate('fecha_recepcion', '>=', $date);
    }
    public function recepcion_to(string $date): Builder
    {
        return $this->query->whereNotNull('fecha_recepcion')->whereDate('fecha_recepcion', '<=', $date);
    }

    /**
     * Filtros por rango de fecha límite.
     */
    public function limite_from(string $date): Builder
    {
        return $this->query->whereNotNull('fecha_limite')->whereDate('fecha_limite', '>=', $date);
    }
    public function limite_to(string $date): Builder
    {
        return $this->query->whereNotNull('fecha_limite')->whereDate('fecha_limite', '<=', $date);
    }

    /**
     * Maneja el ordenamiento de la consulta.
     */
    public function sort(string $column): Builder
    {
        $direction = $this->filters['direction'] ?? 'asc';
        
        $columnMap = [
            'folio' => 'folio_externo',
            'asunto' => 'asunto',
            'status' => 'status',
            'prioridad' => 'prioridad',
            'fechaRegistro' => 'created_at',
            'fechaRecepcion' => 'fecha_recepcion',
            'fechaLimite' => 'fecha_limite',
        ];

        if (isset($columnMap[$column])) {
            return $this->query->orderBy($columnMap[$column], $direction);
        }

        return $this->query;
    }
}