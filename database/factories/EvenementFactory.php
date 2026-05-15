<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvenementFactory extends Factory
{
    protected $model = \App\Models\Evenement::class;

    public function definition(): array
    {
        return [
            'nom_evenement' => $this->faker->title(),
            'description_evenement' => $this->faker->paragraph(),
            'date_debut_evenement' => $this->faker->dateTimeBetween('+1 days', '+30 days'),
            'date_fin_evenement' => $this->faker->dateTimeBetween('+1 days', '+30 days'),
            'lieu_evenement' => $this->faker->address(),
            'organisateur_evenement' => $this->faker->name(),
            'cree_par_utilisateur_id' => User::factory(),
        ];
    }
}
