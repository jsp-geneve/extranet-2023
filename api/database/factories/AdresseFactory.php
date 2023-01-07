<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adresse>
 */
class AdresseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $locales = fn ($country) => match ($country) {
            'Suisse' => 'fr_CH',
            'France' => 'fr_FR',
            default => null,
        };

        return [
            'ligne_1' => fn ($attrs) => fake($locales($attrs['pays']))->streetAddress(),
            'ligne_2' => fn ($attrs) => mt_rand(1,100) < 20 ? fake($locales($attrs['pays']))->secondaryAddress(): null, // @phpstan-ignore-line
            'code_postal' => fn ($attrs) => fake($locales($attrs['pays']))->postcode(), 
            'commune' => fn ($attrs) => fake($locales($attrs['pays']))->city(),
            'pays' => 'Suisse',
        ];
    }
}
