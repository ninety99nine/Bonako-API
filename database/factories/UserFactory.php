<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $lastSeenAt = $this->faker->dateTimeThisMonth();

        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'mobile_number' => $this->generateBotswanaOrangeMobileNumber(),
            'last_seen_at' => $this->faker->dateTimeBetween('-21 days', 'now'),
            'mobile_number_verified_at' => $this->faker->dateTimeBetween('-1 year', $lastSeenAt),
            'is_super_admin' => false,
            'password' => bcrypt('QWEasd'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Generate a valid Botswana Orange mobile number.
     *
     * @return string
     */
    public function generateBotswanaOrangeMobileNumber()
    {
        $number = $this->uniqueRandomNumber(72000000, 72999999);

        // Check if the number is within Orange range
        while (!$this->isBotswanaOrangeMobileNumber($number)) {
            $number = $this->uniqueRandomNumber(72000000, 72999999);
        }

        return '+267'.$number;
    }

    /**
     * Generate a unique random number between the specified range.
     *
     * @param int $min
     * @param int $max
     * @return int
     */
    private function uniqueRandomNumber($min, $max)
    {
        return $this->faker->unique()->numberBetween($min, $max);
    }

    /**
     * Check if a mobile number is within Botswana Orange range.
     *
     * @param string $number
     * @return bool
     */
    private function isBotswanaOrangeMobileNumber($number)
    {
        return (
            (int) $number >= 72000000 &&
            (int) $number <= 72999999 ||
            (int) $number >= 74300000 &&
            (int) $number <= 74499999 ||
            (int) $number >= 74800000 &&
            (int) $number <= 74899999 ||
            (int) $number >= 75000000 &&
            (int) $number <= 75399999 ||
            (int) $number >= 75700000 &&
            (int) $number <= 75799999 ||
            (int) $number >= 76300000 &&
            (int) $number <= 76599999 ||
            (int) $number >= 76900000 &&
            (int) $number <= 76999999 ||
            (int) $number >= 77400000 &&
            (int) $number <= 77599999 ||
            (int) $number >= 77900000 &&
            (int) $number <= 77999999 ||
            (int) $number >= 77300000 &&
            (int) $number <= 77399999
        );
    }
}
