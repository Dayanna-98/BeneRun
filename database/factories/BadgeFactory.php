<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;

class BadgeFactory extends Factory
{
    protected $model = Badge::class;

    public function definition(): array
    {
        return [
            'titre_badge' => $this->faker->unique()->words(2, true),
            'description_badge' => $this->faker->sentence(),
            'icone_badge' => $this->faker->fileExtension(),
            'score_badge' => $this->faker->numberBetween(0, 1000),
            'regle_auto' => $this->faker->optional()->word(),
        ];
    }
}
