<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Client;
use App\Models\IntegrationSetting;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class EmployeeApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $client;
    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем клиента
        $this->client = Client::factory()->create([
            'is_active' => true,
            'subscription_end_date' => now()->addMonth(),
        ]);

        // Создаем сотрудника
        $this->employee = Employee::factory()->create([
            'client_id' => $this->client->id,
        ]);

        // Создаем активную интеграцию для клиента
        IntegrationSetting::factory()->create([
            'client_id' => $this->client->id,
            'is_active' => true,
            'type' => 'webhook',
            'settings' => [
                'webhook_url' => 'https://httpbin.org/post',
                'method' => 'POST',
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ]);
    }

    public function test_employee_data_endpoint_returns_correct_data()
    {
        Sanctum::actingAs($this->employee, ['*']);

        $response = $this->getJson('/api/employee/data');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'employee' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'client' => [
                        'id',
                        'name',
                        'is_active',
                        'subscription_end_date',
                    ],
                ],
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals($this->employee->id, $response->json('data.employee.id'));
        $this->assertEquals($this->client->id, $response->json('data.client.id'));
    }

    public function test_employee_data_endpoint_fails_with_inactive_subscription()
    {
        // Деактивируем подписку
        $this->client->update(['subscription_end_date' => now()->subDays(1)]);

        Sanctum::actingAs($this->employee, ['*']);

        $response = $this->getJson('/api/employee/data');

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Subscription expired',
                'message' => 'Your company subscription has expired. Please contact your administrator.',
                'subscription_expired' => true,
                'code' => 'SUBSCRIPTION_EXPIRED'
            ]);
    }

    public function test_employee_data_endpoint_requires_authentication()
    {
        $response = $this->getJson('/api/employee/data');

        $response->assertStatus(401);
    }

    public function test_submit_resume_endpoint_with_valid_data()
    {
        // Мокаем HTTP-запросы для webhook
        Http::fake([
            'https://httpbin.org/post' => Http::response(['success' => true], 200),
        ]);

        Sanctum::actingAs($this->employee, ['*']);

        Storage::fake('public');
        $file = UploadedFile::fake()->create('resume.pdf', 1000, 'application/pdf');

        $data = [
            'candidate_name' => 'Иван Иванов',
            'candidate_email' => 'ivan.ivanov@gmail.com',
            'candidate_phone' => '+7 (123) 456-78-90',
            'position' => 'Разработчик PHP',
            'resume_file' => $file,
        ];

        $response = $this->post('/api/employee/submit-resume', $data);

        if ($response->status() !== 200) {
            dump('Response status: ' . $response->status());
            dump('Response content: ' . $response->content());
        }

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Резюме успешно отправлено во все системы',
            ]);
    }

    public function test_submit_resume_endpoint_with_invalid_data()
    {
        Sanctum::actingAs($this->employee, ['*']);

        $data = [
            'candidate_name' => '', // Пустое имя
            'candidate_email' => 'invalid-email', // Неверный email
            'candidate_phone' => '123', // Неверный телефон
        ];

        $response = $this->postJson('/api/employee/submit-resume', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['candidate_name', 'candidate_email', 'candidate_phone', 'position']);
    }

    public function test_submit_resume_endpoint_fails_with_inactive_subscription()
    {
        // Деактивируем подписку
        $this->client->update(['subscription_end_date' => now()->subDays(1)]);

        Sanctum::actingAs($this->employee, ['*']);

        Storage::fake('public');
        $file = UploadedFile::fake()->create('resume.pdf', 1000, 'application/pdf');

        $data = [
            'candidate_name' => 'Иван Иванов',
            'candidate_email' => 'ivan.ivanov@gmail.com',
            'candidate_phone' => '+7 (123) 456-78-90',
            'position' => 'Разработчик PHP',
            'resume_file' => $file,
        ];

        $response = $this->postJson('/api/employee/submit-resume', $data);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Subscription expired',
                'message' => 'Your company subscription has expired. Please contact your administrator.',
                'subscription_expired' => true,
                'code' => 'SUBSCRIPTION_EXPIRED'
            ]);
    }

    public function test_submit_resume_endpoint_with_large_file()
    {
        // Мокаем HTTP запросы
        Http::fake([
            'https://httpbin.org/post' => Http::response(['success' => true], 200),
        ]);

        Sanctum::actingAs($this->employee, ['*']);

        Storage::fake('public');
        // Создаем файл размером 12MB (больше допустимого лимита 10MB)
        $file = UploadedFile::fake()->create('resume.pdf', 12000, 'application/pdf');

        $data = [
            'candidate_name' => 'Иван Иванов',
            'candidate_email' => 'ivan.ivanov@gmail.com',
            'candidate_phone' => '+7 (123) 456-78-90',
            'position' => 'Разработчик PHP',
            'resume_file' => $file,
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->post('/api/employee/submit-resume', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resume_file']);
    }

    public function test_submit_resume_endpoint_with_invalid_file_type()
    {
        // Мокаем HTTP запросы
        Http::fake([
            'https://httpbin.org/post' => Http::response(['success' => true], 200),
        ]);

        Sanctum::actingAs($this->employee, ['*']);

        Storage::fake('public');
        // Создаем файл неподдерживаемого типа
        $file = UploadedFile::fake()->create('resume.txt', 1000, 'text/plain');

        $data = [
            'candidate_name' => 'Иван Иванов',
            'candidate_email' => 'ivan.ivanov@gmail.com',
            'candidate_phone' => '+7 (123) 456-78-90',
            'position' => 'Разработчик PHP',
            'resume_file' => $file,
        ];

        $response = $this->postJson('/api/employee/submit-resume', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resume_file']);
    }

    public function test_integration_status_endpoint()
    {
        Sanctum::actingAs($this->employee, ['*']);

        $response = $this->getJson('/api/employee/integration-status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'integrations',
                    'total_integrations',
                    'active_integrations',
                ],
            ]);

        $this->assertTrue($response->json('success'));
    }

    public function test_integration_status_endpoint_with_inactive_subscription()
    {
        // Деактивируем подписку
        $this->client->update(['subscription_end_date' => now()->subDays(1)]);

        Sanctum::actingAs($this->employee, ['*']);

        $response = $this->getJson('/api/employee/integration-status');

        // С неактивной подпиской должен вернуться 403 ошибка из-за middleware
        $response->assertStatus(403);
    }

    public function test_endpoints_require_valid_token()
    {
        // Проверяем что неавторизованные запросы возвращают 401
        $response = $this->getJson('/api/employee/data');
        $response->assertStatus(401);

        $response = $this->getJson('/api/employee/integration-status');
        $response->assertStatus(401);
    }
}
