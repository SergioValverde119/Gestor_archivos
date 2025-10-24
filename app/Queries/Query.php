<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class Query
{
    protected Builder $query;
    protected array $filters;

    public function __construct(Builder $query, array $filters = [])
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    /**
     * Procesa todos los filtros.
     */
    public function handle(): Builder
    {
        foreach ($this->filters as $name => $value) {
            // --- CORRECCIÓN: ---
            // Solo llama al método si existe Y si el valor no es nulo.
            // Esto previene el error "string, null given" en los métodos
            // que esperan un valor, como 'search'.
            if (method_exists($this, $name) && !is_null($value)) {
                $this->$name($value);
            }
        }

        return $this->query;
    }
}
