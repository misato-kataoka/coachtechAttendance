<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => \App\Models\Attendance::factory(),
            'start_time' => $this->faker->dateTimeThisMonth(),
            'end_time' => null,
        ];
    }
}
