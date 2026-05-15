<?php

namespace Database\Factories;

use App\Models\Mission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AffectationFactory extends Factory
{
    protected $model = \App\Models\Affectation::class;

    public function definition(): array
    {
        return [
            'id_utilisateur' => User::factory(),
            'id_mission' => Mission::factory(),
            'statut_affectation' => $this->faker->randomElement(['assigne', 'confirmé', 'présent', 'refusé']),
            'date_affectation' => now(),
        ];
    }
}
