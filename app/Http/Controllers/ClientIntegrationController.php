<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IntegrationSetting;
use App\Services\IntegrationManagerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientIntegrationController extends Controller
{
    protected IntegrationManagerService $integrationManager;

    public function __construct(IntegrationManagerService $integrationManager)
    {
        $this->integrationManager = $integrationManager;
    }

    /**
     * Показать страницу управления интеграциями клиента
     */
    public function index()
    {
        $client = Auth::guard('client')->user();
        $integrations = $client->integrationSettings()->get();

        return view('client.integrations.index', compact('integrations'));
    }

    /**
     * Показать форму создания новой интеграции
     */
    public function create()
    {
        $integrationTypes = [
            'crm' => 'CRM System',
            'telegram' => 'Telegram Bot',
            'webhook' => 'Custom Webhook',
            'email' => 'Email Notifications'
        ];

        return view('client.integrations.create', compact('integrationTypes'));
    }

    /**
     * Сохранить новую интеграцию
     */
    public function store(Request $request)
    {
        $client = Auth::guard('client')->user();

        $request->validate([
            'type' => 'required|in:crm,telegram,webhook,email',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'settings' => 'required|array',
        ]);

        try {
            $integration = IntegrationSetting::create([
                'client_id' => $client->id,
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
                    'client_id' => $client->id,
                    'error' => $testResult['error']
                ]);

                session()->flash('warning', 'Интеграция создана, но тест соединения не прошел: ' . $testResult['error']);
            } else {
                session()->flash('success', 'Интеграция успешно создана и протестирована!');
            }

            return redirect()->route('client.integrations.show', $integration);
        } catch (\Exception $e) {
            Log::error('Failed to create integration', [
                'client_id' => $client->id,
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
        // Проверяем принадлежность интеграции текущему клиенту
        $client = Auth::guard('client')->user();
        if ($integration->client_id !== $client->id) {
            abort(403, 'Доступ запрещен');
        }

        return view('client.integrations.show', compact('integration'));
    }

    /**
     * Показать форму редактирования интеграции
     */
    public function edit(IntegrationSetting $integration)
    {
        // Проверяем принадлежность интеграции текущему клиенту
        $client = Auth::guard('client')->user();
        if ($integration->client_id !== $client->id) {
            abort(403, 'Доступ запрещен');
        }

        $integrationTypes = [
            'crm' => 'CRM System',
            'telegram' => 'Telegram Bot',
            'webhook' => 'Custom Webhook',
            'email' => 'Email Notifications'
        ];

        return view('client.integrations.edit', compact('integration', 'integrationTypes'));
    }

    /**
     * Обновить интеграцию
     */
    public function update(Request $request, IntegrationSetting $integration)
    {
        // Проверяем принадлежность интеграции текущему клиенту
        $client = Auth::guard('client')->user();
        if ($integration->client_id !== $client->id) {
            abort(403, 'Доступ запрещен');
        }

        $request->validate([
            'type' => 'required|in:crm,telegram,webhook,email',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'settings' => 'required|array',
        ]);

        try {
            $integration->update([
                'type' => $request->type,
                'name' => $request->name,
                'is_active' => $request->boolean('is_active'),
                'settings' => $request->settings,
            ]);

            session()->flash('success', 'Интеграция успешно обновлена!');

            return redirect()->route('client.integrations.show', $integration);
        } catch (\Exception $e) {
            Log::error('Failed to update integration', [
                'integration_id' => $integration->id,
                'client_id' => $client->id,
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
        // Проверяем принадлежность интеграции текущему клиенту
        $client = Auth::guard('client')->user();
        if ($integration->client_id !== $client->id) {
            abort(403, 'Доступ запрещен');
        }

        try {
            $integration->delete();

            session()->flash('success', 'Интеграция успешно удалена!');

            return redirect()->route('client.integrations.index');
        } catch (\Exception $e) {
            Log::error('Failed to delete integration', [
                'integration_id' => $integration->id,
                'client_id' => $client->id,
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
        // Проверяем принадлежность интеграции текущему клиенту
        $client = Auth::guard('client')->user();
        if ($integration->client_id !== $client->id) {
            return response()->json(['success' => false, 'error' => 'Доступ запрещен'], 403);
        }

        try {
            $result = $this->integrationManager->testConnection($integration);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Integration connection test failed', [
                'integration_id' => $integration->id,
                'client_id' => $client->id,
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
        // Проверяем принадлежность интеграции текущему клиенту
        $client = Auth::guard('client')->user();
        if ($integration->client_id !== $client->id) {
            return response()->json(['success' => false, 'error' => 'Доступ запрещен'], 403);
        }

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
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка при изменении статуса интеграции'
            ], 500);
        }
    }
}
