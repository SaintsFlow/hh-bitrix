<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'subscription_start_date' => now()->toDateString(),
            'subscription_end_date' => now()->addYear()->toDateString(),
            'max_employees' => fake()->numberBetween(5, 50),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the client's subscription is expired.
     */
    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'subscription_end_date' => now()->subDays(30)->toDateString(),
        ]);
    }

    /**
     * Indicate that the client is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the client has active subscription.
     */
    public function activeSubscription(): static
    {
        return $this->state(fn(array $attributes) => [
            'subscription_start_date' => now()->subMonth()->toDateString(),
            'subscription_end_date' => now()->addYear()->toDateString(),
            'is_active' => true,
        ]);
    }
}
