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
            if (method_exists($this, $name)) {
                $this->$name($value);
            }
        }

        return $this->query;
    }
}