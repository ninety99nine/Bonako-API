<?php

namespace Database\Factories;

use App\Models\FriendGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class FriendGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FriendGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'shared' => $this->faker->boolean(50),
            'can_add_friends' => $this->faker->boolean(50)
        ];
    }
}
