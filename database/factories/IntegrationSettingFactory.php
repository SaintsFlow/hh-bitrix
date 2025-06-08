<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\IntegrationSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IntegrationSetting>
 */
class IntegrationSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([
            IntegrationSetting::TYPE_CRM,
            IntegrationSetting::TYPE_TELEGRAM,
            IntegrationSetting::TYPE_WEBHOOK,
            IntegrationSetting::TYPE_EMAIL,
        ]);

        return [
            'client_id' => Client::factory(),
            'type' => $type,
            'name' => fake()->words(3, true) . ' Integration',
            'is_active' => fake()->boolean(80), // 80% chance of being active
            'settings' => $this->generateSettingsForType($type),
            'webhook_config' => $this->generateWebhookConfig(),
            'webhook_url' => fake()->url(),
            'webhook_method' => fake()->randomElement(['POST', 'PUT']),
            'webhook_headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . fake()->uuid(),
            ],
        ];
    }

    /**
     * Generate type-specific settings.
     */
    private function generateSettingsForType(string $type): array
    {
        switch ($type) {
            case IntegrationSetting::TYPE_CRM:
                return [
                    'api_key' => fake()->uuid(),
                    'base_url' => fake()->url(),
                    'funnel_id' => fake()->numberBetween(1, 100),
                    'stage_id' => fake()->numberBetween(1, 10),
                ];

            case IntegrationSetting::TYPE_TELEGRAM:
                return [
                    'bot_token' => fake()->regexify('[0-9]{10}:[a-zA-Z0-9_-]{35}'),
                    'chat_id' => fake()->randomNumber(9),
                ];

            case IntegrationSetting::TYPE_WEBHOOK:
                return [
                    'retry_attempts' => fake()->numberBetween(1, 5),
                    'timeout' => fake()->numberBetween(5, 30),
                ];

            case IntegrationSetting::TYPE_EMAIL:
                return [
                    'smtp_host' => fake()->domainName(),
                    'smtp_port' => fake()->randomElement([25, 465, 587]),
                    'smtp_username' => fake()->email(),
                    'smtp_password' => fake()->password(),
                ];

            default:
                return [];
        }
    }

    /**
     * Generate webhook configuration.
     */
    private function generateWebhookConfig(): array
    {
        return [
            'retry_attempts' => fake()->numberBetween(1, 5),
            'timeout' => fake()->numberBetween(5, 30),
            'verify_ssl' => fake()->boolean(),
        ];
    }

    /**
     * Indicate that the integration is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the integration is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a CRM integration.
     */
    public function crm(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => IntegrationSetting::TYPE_CRM,
            'name' => 'CRM Integration',
            'settings' => [
                'api_key' => fake()->uuid(),
                'base_url' => 'https://api.crm.example.com',
                'funnel_id' => fake()->numberBetween(1, 100),
                'stage_id' => fake()->numberBetween(1, 10),
            ],
        ]);
    }

    /**
     * Create a Telegram integration.
     */
    public function telegram(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => IntegrationSetting::TYPE_TELEGRAM,
            'name' => 'Telegram Notifications',
            'settings' => [
                'bot_token' => fake()->regexify('[0-9]{10}:[a-zA-Z0-9_-]{35}'),
                'chat_id' => fake()->randomNumber(9),
            ],
        ]);
    }

    /**
     * Create a webhook integration.
     */
    public function webhook(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => IntegrationSetting::TYPE_WEBHOOK,
            'name' => 'Custom Webhook',
            'settings' => [
                'retry_attempts' => fake()->numberBetween(1, 5),
                'timeout' => fake()->numberBetween(5, 30),
            ],
        ]);
    }

    /**
     * Create an email integration.
     */
    public function email(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => IntegrationSetting::TYPE_EMAIL,
            'name' => 'Email Notifications',
            'settings' => [
                'smtp_host' => fake()->domainName(),
                'smtp_port' => fake()->randomElement([25, 465, 587]),
                'smtp_username' => fake()->email(),
                'smtp_password' => fake()->password(),
            ],
        ]);
    }

    /**
     * Create integration for a specific client.
     */
    public function forClient(Client $client): static
    {
        return $this->state(fn(array $attributes) => [
            'client_id' => $client->id,
        ]);
    }
}
