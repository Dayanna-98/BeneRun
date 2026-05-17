<?php

namespace Database\Factories;

use App\Models\Competence;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompetenceFactory extends Factory
{
    protected $model = Competence::class;

    public function definition(): array
    {
        return [
            'nom_competence' => $this->faker->unique()->jobTitle(),
            'types_mission_suggeres' => $this->faker->randomElements(
                ['secours', 'logistique', 'accueil', 'technique', 'animation', 'autre'],
                $this->faker->numberBetween(0, 3)
            ),
        ];
    }
}
