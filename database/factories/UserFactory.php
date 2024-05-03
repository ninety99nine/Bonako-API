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
        $number = '267' . $this->uniqueRandomNumber(72000000, 72999999);

        // Check if the number is within Orange range
        while (!$this->isBotswanaOrangeMobileNumber($number)) {
            $number = '267' . $this->uniqueRandomNumber(72000000, 72999999);
        }

        return $number;
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
            (int) $number >= 26772000000 &&
            (int) $number <= 26772999999 ||
            (int) $number >= 26774300000 &&
            (int) $number <= 26774499999 ||
            (int) $number >= 26774800000 &&
            (int) $number <= 26774899999 ||
            (int) $number >= 26775000000 &&
            (int) $number <= 26775399999 ||
            (int) $number >= 26775700000 &&
            (int) $number <= 26775799999 ||
            (int) $number >= 26776300000 &&
            (int) $number <= 26776599999 ||
            (int) $number >= 26776900000 &&
            (int) $number <= 26776999999 ||
            (int) $number >= 26777400000 &&
            (int) $number <= 26777599999 ||
            (int) $number >= 26777900000 &&
            (int) $number <= 26777999999 ||
            (int) $number >= 26777300000 &&
            (int) $number <= 26777399999
        );
    }
}
