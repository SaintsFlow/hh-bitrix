<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;

class SuperAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super-admin');
    }

    public function dashboard()
    {
        $clientsCount = Client::count();
        $employeesCount = Employee::count();
        $activeClientsCount = Client::where('is_active', true)->count();

        return view('super-admin.dashboard', compact('clientsCount', 'employeesCount', 'activeClientsCount'));
    }

    public function clients(Request $request)
    {
        $query = Client::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $clients = $query->paginate(10);

        return view('super-admin.clients.index', compact('clients'));
    }

    public function createClient()
    {
        return view('super-admin.clients.create');
    }

    public function storeClient(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clients',
            'password' => 'required|string|min:8|confirmed',
            'subscription_start_date' => 'required|date',
            'subscription_end_date' => 'required|date|after:subscription_start_date',
            'max_employees' => 'required|integer|min:1|max:1000',
        ]);

        $client = Client::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'subscription_start_date' => $request->subscription_start_date,
            'subscription_end_date' => $request->subscription_end_date,
            'max_employees' => $request->max_employees,
            'is_active' => true,
        ]);

        $client->assignRole('client');

        return redirect()->route('super-admin.clients')->with('success', 'Клиент успешно создан');
    }

    public function editClient(Client $client)
    {
        return view('super-admin.clients.edit', compact('client'));
    }

    public function updateClient(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clients,email,' . $client->id,
            'subscription_start_date' => 'required|date',
            'subscription_end_date' => 'required|date|after:subscription_start_date',
            'max_employees' => 'required|integer|min:1|max:1000',
        ]);

        $client->update($request->only([
            'name',
            'email',
            'subscription_start_date',
            'subscription_end_date',
            'max_employees'
        ]));

        return redirect()->route('super-admin.clients')->with('success', 'Клиент успешно обновлен');
    }

    public function extendSubscription(Request $request, Client $client)
    {
        $request->validate([
            'subscription_end_date' => 'required|date|after:today',
        ]);

        $oldEndDate = $client->subscription_end_date;

        $client->update([
            'subscription_end_date' => $request->subscription_end_date
        ]);

        // Если подписка была истекшей и теперь продлена - восстанавливаем токены
        if ($oldEndDate < now() && $client->isSubscriptionActive()) {
            $this->restoreEmployeeTokens($client);
        }

        // Отмечаем связанные уведомления как прочитанные
        $client->notifications()->whereIn('type', ['expired', 'warning'])->update(['is_read' => true]);

        activity()
            ->performedOn($client)
            ->causedBy(auth()->user())
            ->withProperties(['old_end_date' => $oldEndDate])
            ->log('Подписка продлена до ' . $request->subscription_end_date);

        return back()->with('success', 'Подписка успешно продлена');
    }

    /**
     * Восстанавливает токены для всех активных сотрудников клиента
     */
    private function restoreEmployeeTokens(Client $client)
    {
        $restoredCount = 0;

        foreach ($client->employees()->where('is_active', true)->get() as $employee) {
            // Проверяем, есть ли у сотрудника токены
            if ($employee->tokens()->count() === 0) {
                // Создаем новый токен
                $token = $employee->createToken('API Token')->plainTextToken;
                $restoredCount++;

                activity()
                    ->performedOn($employee)
                    ->causedBy(auth()->user())
                    ->log('Токен восстановлен после продления подписки');
            }
        }

        if ($restoredCount > 0) {
            activity()
                ->performedOn($client)
                ->causedBy(auth()->user())
                ->withProperties(['restored_tokens' => $restoredCount])
                ->log("Восстановлено токенов: {$restoredCount}");
        }
    }

    public function toggleClientStatus(Client $client)
    {
        $client->update(['is_active' => !$client->is_active]);

        $status = $client->is_active ? 'активирован' : 'деактивирован';
        activity()
            ->performedOn($client)
            ->causedBy(auth()->user())
            ->log("Клиент {$status}");

        return back()->with('success', "Клиент успешно {$status}");
    }

    public function employees(Request $request)
    {
        $query = Employee::with('client');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $employees = $query->paginate(10);

        return view('super-admin.employees.index', compact('employees'));
    }

    public function createEmployee()
    {
        $clients = Client::where('is_active', true)->get();
        return view('super-admin.employees.create', compact('clients'));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $client = Client::findOrFail($request->client_id);

        if (!$client->canAddEmployee()) {
            return back()->withErrors(['client_id' => 'У клиента достигнут лимит сотрудников']);
        }

        $employee = Employee::create([
            'client_id' => $request->client_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        $employee->assignRole('employee');

        return redirect()->route('super-admin.employees')->with('success', 'Сотрудник успешно создан');
    }

    public function editEmployee(Employee $employee)
    {
        $clients = Client::where('is_active', true)->get();
        return view('super-admin.employees.edit', compact('employee', 'clients'));
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees,email,' . $employee->id,
        ]);

        $employee->update($request->only(['client_id', 'name', 'email']));

        return redirect()->route('super-admin.employees')->with('success', 'Сотрудник успешно обновлен');
    }

    public function generateToken(Employee $employee)
    {
        // Удаляем старые токены
        $employee->tokens()->delete();

        // Создаем новый токен
        $token = $employee->createToken('employee-token')->plainTextToken;

        activity()
            ->performedOn($employee)
            ->causedBy(auth()->user())
            ->log('Выпущен новый токен');

        return back()->with('success', 'Новый токен сгенерирован')->with('token', $token);
    }

    public function revokeToken(Employee $employee)
    {
        $employee->tokens()->delete();

        activity()
            ->performedOn($employee)
            ->causedBy(auth()->user())
            ->log('Токен отозван');

        return back()->with('success', 'Токен успешно отозван');
    }

    public function activityLog(Request $request)
    {
        $query = Activity::with(['subject', 'causer']);

        if ($request->filled('model')) {
            $query->where('subject_type', $request->model);
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('super-admin.activity-log', compact('activities'));
    }

    public function destroyEmployee(Employee $employee)
    {
        // Сохраняем информацию для логирования
        $employeeName = $employee->name;
        $clientName = $employee->client->name;

        // Отзываем все токены перед удалением
        $employee->tokens()->delete();

        // Логируем удаление ПЕРЕД удалением модели
        activity()
            ->performedOn($employee)
            ->causedBy(auth()->user())
            ->withProperties([
                'employee_name' => $employeeName,
                'client_name' => $clientName,
                'employee_id' => $employee->id,
                'client_id' => $employee->client_id
            ])
            ->log('Сотрудник удален супер-админом');

        $employee->delete();

        return redirect()->route('super-admin.employees')->with('success', 'Сотрудник успешно удален');
    }
}
