<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'start_time' => $this->faker->dateTimeThisMonth(),
            'end_time' => null,
            'work_date' => function (array $attributes) {
                return \Carbon\Carbon::parse($attributes['start_time'])->toDateString();
            },
        ];
    }
}
