<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\IntegrationSetting;
use App\Services\IntegrationManagerService;
use Illuminate\Support\Facades\Log;

class IntegrationController extends Controller
{
    protected IntegrationManagerService $integrationManager;

    public function __construct(IntegrationManagerService $integrationManager)
    {
        $this->integrationManager = $integrationManager;
    }

    /**
     * Показать страницу управления интеграциями для супер-админа
     */
    public function index(Request $request)
    {
        $clients = Client::with('integrationSettings')->get();

        return view('super-admin.integrations.index', compact('clients'));
    }

    /**
     * Показать форму создания новой интеграции
     */
    public function create(Request $request)
    {
        $client_id = $request->get('client_id');
        $client = $client_id ? Client::findOrFail($client_id) : null;
        $clients = Client::all();

        $integrationTypes = [
            'crm' => 'CRM System',
            'telegram' => 'Telegram Bot',
            'webhook' => 'Custom Webhook',
            'email' => 'Email Notifications'
        ];

        return view('super-admin.integrations.create', compact('client', 'clients', 'integrationTypes'));
    }

    /**
     * Сохранить новую интеграцию
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|in:crm,telegram,webhook,email',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'settings' => 'required|array',
        ]);

        try {
            $integration = IntegrationSetting::create([
                'client_id' => $request->client_id,
                'type' => $request->type,
                'name' => $request->name,
                'is_active' => $request->boolean('is_active', true),
                'settings' => $request->settings,
            ]);

            // Тестируем соединение
            $testResult = $this->integrationManager->testConnection($integration);

            if (!$testResult['success']) {
                Log::warning('Integration connection test failed', [
                    'integration_id' => $integration->id,
                    'error' => $testResult['error']
                ]);

                session()->flash('warning', 'Интеграция создана, но тест соединения не прошел: ' . $testResult['error']);
            } else {
                session()->flash('success', 'Интеграция успешно создана и протестирована!');
            }

            return redirect()->route('super-admin.integrations.show', $integration);
        } catch (\Exception $e) {
            Log::error('Failed to create integration', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'Ошибка при создании интеграции: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Показать детали интеграции
     */
    public function show(IntegrationSetting $integration)
    {
        $integration->load('client');

        // Подсчитываем статистику использования API из логов активности
        $apiStats = $this->calculateApiStats($integration);

        return view('super-admin.integrations.show', compact('integration', 'apiStats'));
    }

    /**
     * Подсчитать статистику использования API для интеграции
     */
    private function calculateApiStats(IntegrationSetting $integration)
    {
        $clientId = $integration->client_id;

        // Получаем все логи активности для API endpoints этого клиента
        $query = \Spatie\Activitylog\Models\Activity::where('properties->via_api', true)
            ->whereHas('subject', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });

        // Всего API запросов
        $totalApiRequests = $query->count();

        // API запросы за сегодня
        $todayApiRequests = (clone $query)->whereDate('created_at', today())->count();

        // Статистика по типам активности
        $apiByType = (clone $query)->get()
            ->groupBy('description')
            ->map(function ($activities) {
                return $activities->count();
            });

        // Статистика отправки резюме (самая важная для интеграций)
        $resumeSubmissions = (clone $query)
            ->where('description', 'Отправлено резюме через API')
            ->count();

        $resumeSubmissionsToday = (clone $query)
            ->where('description', 'Отправлено резюме через API')
            ->whereDate('created_at', today())
            ->count();

        return [
            'total_api_requests' => $totalApiRequests,
            'today_api_requests' => $todayApiRequests,
            'resume_submissions_total' => $resumeSubmissions,
            'resume_submissions_today' => $resumeSubmissionsToday,
            'api_by_type' => $apiByType,
        ];
    }

    /**
     * Показать форму редактирования интеграции
     */
    public function edit(IntegrationSetting $integration)
    {
        $integration->load('client');
        $clients = Client::all();

        $integrationTypes = [
            'crm' => 'CRM System',
            'telegram' => 'Telegram Bot',
            'webhook' => 'Custom Webhook',
            'email' => 'Email Notifications'
        ];

        return view('super-admin.integrations.edit', compact('integration', 'clients', 'integrationTypes'));
    }

    /**
     * Обновить интеграцию
     */
    public function update(Request $request, IntegrationSetting $integration)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|in:crm,telegram,webhook,email',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'settings' => 'required|array',
        ]);

        try {
            $integration->update([
                'client_id' => $request->client_id,
                'type' => $request->type,
                'name' => $request->name,
                'is_active' => $request->boolean('is_active'),
                'settings' => $request->settings,
            ]);

            session()->flash('success', 'Интеграция успешно обновлена!');

            return redirect()->route('super-admin.integrations.show', $integration);
        } catch (\Exception $e) {
            Log::error('Failed to update integration', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'Ошибка при обновлении интеграции: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Удалить интеграцию
     */
    public function destroy(IntegrationSetting $integration)
    {
        try {
            $integration->delete();

            session()->flash('success', 'Интеграция успешно удалена!');

            return redirect()->route('super-admin.integrations.index');
        } catch (\Exception $e) {
            Log::error('Failed to delete integration', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Ошибка при удалении интеграции: ' . $e->getMessage()]);
        }
    }

    /**
     * Тестировать соединение с интеграцией
     */
    public function testConnection(IntegrationSetting $integration)
    {
        try {
            $result = $this->integrationManager->testConnection($integration);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Integration connection test failed', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка при тестировании соединения: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Переключить статус активности интеграции
     */
    public function toggleStatus(IntegrationSetting $integration)
    {
        try {
            $integration->update(['is_active' => !$integration->is_active]);

            $status = $integration->is_active ? 'активирована' : 'деактивирована';

            return response()->json([
                'success' => true,
                'message' => "Интеграция {$status}",
                'is_active' => $integration->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle integration status', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка при изменении статуса интеграции'
            ], 500);
        }
    }
}
