<?php

namespace Database\Factories;

use App\Models\Certificat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CertificatFactory extends Factory
{
    protected $model = Certificat::class;

    public function definition(): array
    {
        return [
            'id_utilisateur' => User::factory(),
            'titre_certificat' => $this->faker->words(3, true),
            'emetteur_certificat' => $this->faker->company(),
            'date_emission_certificat' => $this->faker->date(),
            'date_expiration_certificat' => $this->faker->optional()->dateTimeBetween('now', '+5 years'),
            'type_certificat' => $this->faker->randomElement(['platform', 'external']),
            'statut_certificat' => $this->faker->randomElement([
                'en attente',
                'approuvé',
                'rejeté',
            ]),
            'chemin_fichier_certificat' => $this->faker->optional()->filePath(),
        ];
    }
}
