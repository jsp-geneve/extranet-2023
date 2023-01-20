<?php

namespace Database\Seeders;

use App\Models\Adresse;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adresses = Adresse::all();
        $users = User::all()->shuffle();
        $locales = fn ($country) => match ($country) {
            'Suisse' => 'fr_CH',
            'France' => 'fr_FR',
            default => null,
        };

        Contact::factory()
            ->count(100)
            ->make() // save() dans le prochain callback
            ->each(
                fn (Contact $contact) => $contact
                    ->adresse()
                    ->associate($adresses->random()) // random() afin de retomber parfois sur la même Adresse
                    ->tap(
                        fn (Contact $contact) => $contact->telephone = fake($locales($contact->adresse->pays))->phoneNumber()
                    )
                    ->user()
                    ->associate($users->pop()) // pop() afin de ne jamais retomber sur le même User
                    ->save()
            );
    }
}
