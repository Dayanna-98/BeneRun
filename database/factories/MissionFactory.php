<?php

namespace Database\Factories;

use App\Models\Evenement;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MissionFactory extends Factory
{
    protected $model = Mission::class;

    public function definition(): array
    {
        $startTime = $this->faker->time('H:i');

        return [
            'titre_mission' => $this->faker->title(),
            'description_mission' => $this->faker->paragraph(),
            'date_mission' => $this->faker->dateTimeBetween('+1 days', '+30 days'),
            'lieu_mission' => $this->faker->address(),
            'statut_mission' => 'À venir',
            'type_mission' => 'logistique',
            'heure_debut_mission' => $startTime,
            'heure_fin_mission' => $this->faker->time('H:i'),
            'id_evenement' => Evenement::factory(),
            'responsable_utilisateur_id' => User::factory(),
            'nombre_benevoles_max' => $this->faker->numberBetween(5, 50),
        ];
    }
}
