<?php

namespace Database\Seeders;

use App\Models\Adresse;
use Illuminate\Database\Seeder;

class AdresseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Adresse::factory()
            ->count(50)
            ->sequence(fn () => [
                'pays' => mt_rand(0, 100) < 20 ? 'France' : 'Suisse'
            ])
            ->create();
    }
}
