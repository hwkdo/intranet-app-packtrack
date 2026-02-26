<?php

namespace Hwkdo\IntranetAppPacktrack\Database\Factories;

use App\Models\User;
use Hwkdo\IntranetAppPacktrack\Models\Packetdienst;
use Hwkdo\IntranetAppPacktrack\Models\Paket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paket>
 */
class PaketFactory extends Factory
{
    protected $model = Paket::class;

    public function definition(): array
    {
        return [
            'nr' => strtoupper(fake()->bothify('??##########')),
            'empfaenger_id' => User::factory(),
            'annehmer_id' => User::factory(),
            'packetdienst_id' => Packetdienst::factory(),
            'lieferant' => fake()->optional()->company(),
            'bemerkung' => fake()->optional()->sentence(),
        ];
    }

    public function abgeholt(): static
    {
        return $this->afterCreating(function (Paket $paket) {
            AbholungFactory::new()->create(['paket_id' => $paket->id]);
        });
    }
}
