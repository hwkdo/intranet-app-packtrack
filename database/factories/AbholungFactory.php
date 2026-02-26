<?php

namespace Hwkdo\IntranetAppPacktrack\Database\Factories;

use App\Models\User;
use Hwkdo\IntranetAppPacktrack\Models\Abholung;
use Hwkdo\IntranetAppPacktrack\Models\Paket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Abholung>
 */
class AbholungFactory extends Factory
{
    protected $model = Abholung::class;

    public function definition(): array
    {
        return [
            'paket_id' => Paket::factory(),
            'ausgeber_id' => User::factory(),
            'abholer_id' => User::factory(),
            'bemerkung' => fake()->optional()->sentence(),
        ];
    }
}
