<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitResumeRequest;
use App\Services\IntegrationManagerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class EmployeeApiController extends Controller
{
    public function __construct(
        private IntegrationManagerService $integrationManager
    ) {
        $this->middleware(['auth:sanctum', 'token.subscription']);
    }

    /**
     * Получить данные сотрудника и клиента
     */
    public function getEmployeeData(Request $request): JsonResponse
    {
        $employee = $request->user();
        $client = $employee->client;

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'position' => $employee->position ?? null,
                    'phone' => $employee->phone ?? null,
                    'is_active' => $employee->is_active,
                    'created_at' => $employee->created_at,
                    'last_login_at' => $employee->last_login_at,
                ],
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'subscription_start_date' => $client->subscription_start_date,
                    'subscription_end_date' => $client->subscription_end_date,
                    'is_active' => $client->isSubscriptionActive(),
                    'subscription_expires_in_days' => $client->subscription_end_date
                        ? Carbon::parse($client->subscription_end_date)->diffInDays(now(), false)
                        : null,
                    'max_employees' => $client->max_employees,
                    'current_employees_count' => $client->employees()->count(),
                ],
                'token_info' => [
                    'name' => $request->user()->currentAccessToken()->name,
                    'created_at' => $request->user()->currentAccessToken()->created_at,
                    'last_used_at' => $request->user()->currentAccessToken()->last_used_at,
                ]
            ]
        ]);
    }

    /**
     * Отправить резюме в внешние системы
     */
    public function submitResume(SubmitResumeRequest $request): JsonResponse
    {
        $employee = $request->user();
        $client = $employee->client;

        // Подготавливаем данные резюме
        $resumeData = [
            'candidate_name' => $request->candidate_name,
            'candidate_phone' => $request->candidate_phone,
            'candidate_email' => $request->candidate_email,
            'position' => $request->position,
            'source' => $request->source ?? 'API',
            'notes' => $request->notes,
            'submitted_by' => [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'employee_email' => $employee->email,
                'client_id' => $client->id,
                'client_name' => $client->name,
            ],
            'submitted_at' => now()->toISOString(),
        ];

        // Обрабатываем файл резюме если загружен
        if ($request->hasFile('resume_file')) {
            $file = $request->file('resume_file');

            // Валидируем файл
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid file upload',
                    'message' => 'Загруженный файл поврежден или недоступен'
                ], 400);
            }

            if (!in_array($file->getClientOriginalExtension(), ['pdf', 'doc', 'docx'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid file format',
                    'message' => 'Поддерживаются только файлы PDF, DOC, DOCX'
                ], 400);
            }

            // Читаем содержимое файла для отправки
            $resumeData['resume_file'] = [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'content' => base64_encode($file->getContent()),
            ];
        }

        try {
            // Отправляем данные во все активные интеграции клиента
            $results = $this->integrationManager->sendResumeData($client, $resumeData);

            // Подготавливаем ответ
            $successCount = collect($results)->where('success', true)->count();
            $totalCount = count($results);

            return response()->json([
                'success' => $successCount > 0,
                'message' => $successCount === $totalCount
                    ? 'Резюме успешно отправлено во все системы'
                    : "Резюме отправлено в {$successCount} из {$totalCount} систем",
                'data' => [
                    'sent_to_systems' => $successCount,
                    'total_systems' => $totalCount,
                    'results' => $results,
                    'resume_id' => uniqid('resume_'),
                    'submitted_at' => now()->toISOString(),
                ]
            ], $successCount > 0 ? 200 : 422);
        } catch (\Exception $e) {
            \Log::error('Resume submission failed', [
                'employee_id' => $employee->id,
                'client_id' => $client->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'Произошла ошибка при отправке резюме. Попробуйте позже.',
                'debug' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Получить статус интеграций клиента
     */
    public function getIntegrationStatus(Request $request): JsonResponse
    {
        $employee = $request->user();
        $client = $employee->client;

        $integrations = $client->activeIntegrations()->get()->map(function ($integration) {
            return [
                'id' => $integration->id,
                'name' => $integration->name,
                'type' => $integration->type,
                'is_active' => $integration->is_active,
                'last_used_at' => $integration->last_used_at,
                'status' => $integration->is_active ? 'active' : 'inactive',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'integrations' => $integrations,
                'total_integrations' => $integrations->count(),
                'active_integrations' => $integrations->where('is_active', true)->count(),
            ]
        ]);
    }
}
