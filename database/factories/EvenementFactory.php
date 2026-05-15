<?php

namespace Database\Factories;

use App\Models\Evenement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvenementFactory extends Factory
{
    protected $model = Evenement::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 days', '+30 days');
        $endDate = $this->faker->dateTimeBetween($startDate, '+60 days');

        return [
            'nom_evenement' => $this->faker->title(),
            'description_evenement' => $this->faker->paragraph(),
            'date_debut_evenement' => $startDate,
            'date_fin_evenement' => $endDate,
            'lieu_evenement' => $this->faker->address(),
            'organisateur_evenement' => $this->faker->name(),
            'mode_localisation_evenement' => 'manual',
            'google_maps_url_evenement' => 'https://www.google.com/maps?q=46.2044,6.1432',
            'rayon_localisation_evenement' => 500,
            'nombre_benevoles_requis' => $this->faker->numberBetween(20, 200),
            'cree_par_utilisateur_id' => User::factory(),
        ];
    }
}
