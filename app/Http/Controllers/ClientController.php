<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:client');
    }

    public function dashboard()
    {
        $client = Auth::guard('client')->user();
        $employeesCount = $client->employees()->count();
        $activeEmployeesCount = $client->employees()->where('is_active', true)->count();

        return view('client.dashboard', compact('client', 'employeesCount', 'activeEmployeesCount'));
    }

    public function employees(Request $request)
    {
        $client = Auth::guard('client')->user();
        $query = $client->employees();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $employees = $query->paginate(10);

        return view('client.employees.index', compact('employees', 'client'));
    }

    public function createEmployee()
    {
        $client = Auth::guard('client')->user();

        if (!$client->canAddEmployee()) {
            return redirect()->route('client.employees.index')->withErrors(['limit' => 'Достигнут лимит сотрудников']);
        }

        return view('client.employees.create', compact('client'));
    }

    public function storeEmployee(Request $request)
    {
        $client = Auth::guard('client')->user();

        if (!$client->canAddEmployee()) {
            return back()->withErrors(['limit' => 'Достигнут лимит сотрудников']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $employee = Employee::create([
            'client_id' => $client->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        $employee->assignRole('employee');

        return redirect()->route('client.employees.index')->with('success', 'Сотрудник успешно создан');
    }

    public function editEmployee(Employee $employee)
    {
        $client = Auth::guard('client')->user();

        if ($employee->client_id !== $client->id) {
            abort(403);
        }

        return view('client.employees.edit', compact('employee', 'client'));
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $client = Auth::guard('client')->user();

        if ($employee->client_id !== $client->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees,email,' . $employee->id,
        ]);

        $employee->update($request->only(['name', 'email']));

        return redirect()->route('client.employees.index')->with('success', 'Сотрудник успешно обновлен');
    }

    public function generateToken(Employee $employee)
    {
        $client = Auth::guard('client')->user();

        if ($employee->client_id !== $client->id) {
            abort(403);
        }

        // Удаляем старые токены
        $employee->tokens()->delete();

        // Создаем новый токен
        $token = $employee->createToken('employee-token')->plainTextToken;

        activity()
            ->performedOn($employee)
            ->causedBy($client)
            ->log('Выпущен новый токен клиентом');

        return back()->with('success', 'Новый токен сгенерирован')->with('token', $token);
    }

    public function refreshToken(Employee $employee)
    {
        return $this->generateToken($employee);
    }

    public function destroyEmployee(Employee $employee)
    {
        $client = Auth::guard('client')->user();

        if ($employee->client_id !== $client->id) {
            abort(403);
        }

        // Отзываем все токены перед удалением
        $employee->tokens()->delete();

        $employee->delete();

        activity()
            ->performedOn($employee)
            ->causedBy($client)
            ->log('Сотрудник удален клиентом');

        return redirect()->route('client.employees.index')->with('success', 'Сотрудник успешно удален');
    }

    public function issueToken(Employee $employee)
    {
        $client = Auth::guard('client')->user();

        if ($employee->client_id !== $client->id) {
            abort(403);
        }

        // Создаем новый токен (не удаляя старые, если они есть)
        $token = $employee->createToken('employee-token-' . now()->timestamp)->plainTextToken;

        activity()
            ->performedOn($employee)
            ->causedBy($client)
            ->log('Выдан новый токен сотруднику');

        return back()->with('success', 'Токен успешно выдан сотруднику')->with('token', $token);
    }

    public function revokeToken(Employee $employee)
    {
        $client = Auth::guard('client')->user();

        if ($employee->client_id !== $client->id) {
            abort(403);
        }

        // Отзываем все токены сотрудника
        $tokensCount = $employee->tokens()->count();
        $employee->tokens()->delete();

        activity()
            ->performedOn($employee)
            ->causedBy($client)
            ->log('Отозваны все токены сотрудника');

        return back()->with('success', "Отозвано токенов: {$tokensCount}");
    }

    public function activityLog(Request $request)
    {
        $client = Auth::guard('client')->user();

        $query = Activity::where(function ($q) use ($client) {
            $q->where('subject_type', Client::class)->where('subject_id', $client->id)
                ->orWhere('subject_type', Employee::class)
                ->whereIn('subject_id', $client->employees()->pluck('id'));
        });

        $activities = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('client.activity-log', compact('activities'));
    }
}
