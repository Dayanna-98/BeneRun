<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom_utilisateur' => fake()->lastName(),
            'prenom_utilisateur' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role_utilisateur' => 'bénévole',
            'telephone_utilisateur' => fake()->optional()->phoneNumber(),
            'adresse_utilisateur' => fake()->optional()->address(),
            'date_naissance_utilisateur' => fake()->optional()->date(),
            'allergies_utilisateur' => null,
            'problemes_sante_utilisateur' => null,
            'possede_permis_utilisateur' => false,
            'est_motorise_utilisateur' => false,
            'possede_vehicule_utilisateur' => false,
            'taille_tshirt_utilisateur' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL']),
            'est_anonyme_utilisateur' => false,
            'est_suspendu_utilisateur' => false,
            'raison_suspension_utilisateur' => null,
            'permissions_utilisateur' => 'manageSkills,manageCertificates,favoriteMission',
            'nombre_missions_utilisateur' => 0,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
