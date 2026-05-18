<?php

namespace Database\Factories;

use App\Models\Mission;
use App\Models\Postulation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostulationFactory extends Factory
{
    protected $model = Postulation::class;

    public function definition(): array
    {
        return [
            'id_mission' => Mission::factory(),
            'id_utilisateur' => User::factory(),
            'statut_postulation' => $this->faker->randomElement([
                'en_attente',
                'accepte',
                'rejete',
                'annule',
            ]),
            'date_postulation' => $this->faker->dateTime(),
            'date_decision' => $this->faker->optional()->dateTime(),
            'date_annulation' => $this->faker->optional()->dateTime(),
        ];
    }
}
