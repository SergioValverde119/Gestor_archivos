<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Aplica los filtros a la consulta.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        // Resuelve la clase de filtros específica para este modelo.
        // Por ejemplo, para el modelo 'Oficio', buscará 'OficioQuery'.
        $queryClass = $this->getQueryClass();

        if (class_exists($queryClass)) {
            (new $queryClass($query, $filters))->handle();
        }

        return $query;
    }

    /**
     * Determina el nombre de la clase de filtros para el modelo actual.
     */
    protected function getQueryClass(): string
    {
        // Obtiene el nombre de la clase del modelo (ej. "Oficio")
        $modelName = class_basename($this);
        
        // Construye el nombre completo de la clase de filtros
        // (ej. "App\Queries\OficioQuery")
        return 'App\\Queries\\' . $modelName . 'Query';
    }
}