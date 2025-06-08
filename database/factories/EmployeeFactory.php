<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
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
            'client_id' => Client::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => true,
            'last_login_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the employee is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the employee belongs to a specific client.
     */
    public function forClient(Client $client): static
    {
        return $this->state(fn(array $attributes) => [
            'client_id' => $client->id,
        ]);
    }

    /**
     * Indicate that the employee has never logged in.
     */
    public function neverLoggedIn(): static
    {
        return $this->state(fn(array $attributes) => [
            'last_login_at' => null,
        ]);
    }
}
