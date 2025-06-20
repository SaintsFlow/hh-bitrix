<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\IntegrationSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

class IntegrationManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем роли для разных гвардов
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::create(['name' => 'client', 'guard_name' => 'client']);

        // Создаем супер-администратора
        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');

        // Создаем клиента (Client уже является Authenticatable, не нужен отдельный User)
        $this->client = Client::factory()->create([
            'is_active' => true,
            'subscription_end_date' => now()->addMonth(),
        ]);
        $this->client->assignRole('client');
    }

    public function test_super_admin_can_view_integrations_index()
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('super-admin.integrations.index'));

        $response->assertStatus(200)
            ->assertViewIs('super-admin.integrations.index')
            ->assertViewHas('clients');
    }

    public function test_super_admin_can_create_integration()
    {
        $this->withoutMiddleware();
        $this->actingAs($this->superAdmin);

        $integrationData = [
            'client_id' => $this->client->id,
            'type' => 'crm',
            'name' => 'Test CRM Integration',
            'description' => 'Test integration description',
            'is_active' => true,
            'settings' => [
                'crm_url' => 'https://test-crm.com',
                'api_key' => 'test-api-key',
                'funnel_id' => '1',
                'stage_id' => 'NEW',
            ],
        ];

        $response = $this->post(route('super-admin.integrations.store'), $integrationData);

        $response->assertStatus(302); // Redirect after creation

        $this->assertDatabaseHas('integration_settings', [
            'client_id' => $this->client->id,
            'type' => 'crm',
            'name' => 'Test CRM Integration',
        ]);
    }

    public function test_super_admin_can_update_integration()
    {
        // Create session to avoid CSRF issues
        $this->session(['_token' => 'test-token']);

        $this->actingAs($this->superAdmin);

        $integration = IntegrationSetting::factory()->create([
            'client_id' => $this->client->id,
            'type' => 'telegram',
            'name' => 'Original Name',
        ]);

        $updateData = [
            '_token' => 'test-token',
            'client_id' => $this->client->id,
            'type' => 'telegram',
            'name' => 'Updated Name',
            'is_active' => false,
            'settings' => [
                'bot_token' => 'updated-token',
                'chat_id' => 'updated-chat-id',
            ],
        ];

        $response = $this->put(route('super-admin.integrations.update', $integration), $updateData);

        $response->assertStatus(302); // Redirect after update

        $this->assertDatabaseHas('integration_settings', [
            'id' => $integration->id,
            'name' => 'Updated Name',
            'is_active' => 0, // SQLite stores boolean as 0/1
        ]);
    }

    public function test_super_admin_can_delete_integration()
    {
        // Create session to avoid CSRF issues
        $this->session(['_token' => 'test-token']);

        $this->actingAs($this->superAdmin);

        $integration = IntegrationSetting::factory()->create([
            'client_id' => $this->client->id,
        ]);

        $response = $this->delete(route('super-admin.integrations.destroy', $integration), [
            '_token' => 'test-token'
        ]);

        $response->assertStatus(302); // Redirect after deletion

        $this->assertDatabaseMissing('integration_settings', [
            'id' => $integration->id,
        ]);
    }

    public function test_client_can_view_own_integrations()
    {
        $this->actingAs($this->client, 'client');

        // Создаем интеграции для клиента
        IntegrationSetting::factory()->count(3)->create([
            'client_id' => $this->client->id,
        ]);

        // Создаем интеграцию для другого клиента
        $otherClient = Client::factory()->create();
        IntegrationSetting::factory()->create([
            'client_id' => $otherClient->id,
        ]);

        $response = $this->get(route('client.integrations.index'));

        $response->assertStatus(200)
            ->assertViewIs('client.integrations.index')
            ->assertViewHas('integrations');

        $integrations = $response->viewData('integrations');
        $this->assertCount(3, $integrations);

        // Проверяем, что все интеграции принадлежат текущему клиенту
        foreach ($integrations as $integration) {
            $this->assertEquals($this->client->id, $integration->client_id);
        }
    }

    public function test_client_can_create_integration()
    {
        $this->withoutMiddleware();
        $this->actingAs($this->client, 'client');

        $integrationData = [
            'type' => 'webhook',
            'name' => 'Client Webhook Integration',
            'description' => 'Client integration description',
            'is_active' => true,
            'settings' => [
                'webhook_url' => 'https://client-webhook.com',
                'method' => 'POST',
                'timeout' => 30,
            ],
        ];

        $response = $this->post(route('client.integrations.store'), $integrationData);

        $response->assertStatus(302); // Redirect after creation

        $this->assertDatabaseHas('integration_settings', [
            'client_id' => $this->client->id,
            'type' => 'webhook',
            'name' => 'Client Webhook Integration',
        ]);
    }

    public function test_client_cannot_access_other_clients_integrations()
    {
        $this->actingAs($this->client, 'client');

        // Создаем интеграцию для другого клиента
        $otherClient = Client::factory()->create();
        $otherIntegration = IntegrationSetting::factory()->create([
            'client_id' => $otherClient->id,
        ]);

        $response = $this->get(route('client.integrations.show', $otherIntegration));
        $response->assertStatus(403); // Forbidden

        $response = $this->get(route('client.integrations.edit', $otherIntegration));
        $response->assertStatus(403); // Forbidden
    }

    public function test_client_can_toggle_integration_status()
    {
        // Create session to avoid CSRF issues
        $this->session(['_token' => 'test-token']);

        $this->actingAs($this->client, 'client');

        $integration = IntegrationSetting::factory()->create([
            'client_id' => $this->client->id,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('client.integrations.toggle', $integration), [
            '_token' => 'test-token'
        ]);

        $response->assertStatus(200); // JSON response for toggle action

        $this->assertDatabaseHas('integration_settings', [
            'id' => $integration->id,
            'is_active' => 0, // SQLite stores boolean as 0/1
        ]);

        // Тестируем повторное переключение
        $response = $this->postJson(route('client.integrations.toggle', $integration), [
            '_token' => 'test-token'
        ]);

        $this->assertDatabaseHas('integration_settings', [
            'id' => $integration->id,
            'is_active' => 1, // SQLite stores boolean as 0/1
        ]);
    }

    public function test_integration_test_connection_returns_json()
    {
        // Create session to avoid CSRF issues
        $this->session(['_token' => 'test-token']);

        $this->actingAs($this->client, 'client');

        // Создаем простую интеграцию для тестирования
        $integration = IntegrationSetting::factory()->create([
            'client_id' => $this->client->id,
            'type' => 'webhook',
            'settings' => [
                'webhook_url' => 'https://example.com/webhook',
                'method' => 'POST',
                'timeout' => 5,
            ],
        ]);

        // Подставляем мок для HTTP-запросов
        Http::fake([
            'example.com/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $response = $this->postJson(route('client.integrations.test', $integration), [
            '_token' => 'test-token'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
            ]);
    }

    public function test_guest_cannot_access_integration_management()
    {
        $response = $this->get(route('super-admin.integrations.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('client.integrations.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_client_with_inactive_subscription_sees_warning()
    {
        // Деактивируем подписку клиента (делаем дату окончания в прошлом)
        $this->client->update([
            'subscription_end_date' => now()->subDay(),
        ]);

        $this->actingAs($this->client, 'client');

        $response = $this->get(route('client.integrations.index'));

        // Middleware должен перенаправить на dashboard с сообщением о подписке
        $response->assertStatus(302)
            ->assertRedirect(route('client.dashboard'))
            ->assertSessionHas('subscription_expired');
    }

    public function test_integration_validation_rules()
    {
        $this->withoutMiddleware();
        $this->actingAs($this->client, 'client');

        // Тестируем валидацию обязательных полей
        $response = $this->post(route('client.integrations.store'), []);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['type', 'name', 'settings']);

        // Тестируем валидацию неверного типа
        $response = $this->post(route('client.integrations.store'), [
            'type' => 'invalid_type',
            'name' => 'Test Integration',
            'settings' => [],
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['type']);
    }
}
