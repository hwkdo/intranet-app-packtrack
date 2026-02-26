<?php

namespace Hwkdo\IntranetAppPacktrack\Database\Factories;

use Hwkdo\IntranetAppPacktrack\Models\Packetdienst;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Packetdienst>
 */
class PacketdienstFactory extends Factory
{
    protected $model = Packetdienst::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['DHL', 'UPS', 'GLS', 'DPD', 'Hermes', 'FedEx']),
        ];
    }
}
