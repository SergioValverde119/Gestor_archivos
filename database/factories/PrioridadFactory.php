<?php

namespace Database\Factories;

use App\Models\Prioridad;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrioridadFactory extends Factory
{
    protected $model = Prioridad::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->unique()->randomElement(['Urgente', 'Normal', 'Baja']),
        ];
    }
}
