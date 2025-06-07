<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:employee');
    }

    public function dashboard()
    {
        $employee = Auth::guard('employee')->user();
        $client = $employee->client;

        return view('employee.dashboard', compact('employee', 'client'));
    }

    public function profile()
    {
        $employee = Auth::guard('employee')->user();
        $client = $employee->client;

        return view('employee.profile', compact('employee', 'client'));
    }

    public function editProfile()
    {
        $employee = Auth::guard('employee')->user();
        return view('employee.profile.edit', compact('employee'));
    }

    public function updateProfile(Request $request)
    {
        $employee = Auth::guard('employee')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees,email,' . $employee->id,
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Проверяем текущий пароль если указан новый
        if ($request->filled('password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $employee->password)) {
                return back()->withErrors(['current_password' => 'Неверный текущий пароль']);
            }
        }

        $updateData = $request->only(['name', 'email', 'position', 'phone']);

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $employee->update($updateData);

        return back()->with('success', 'Профиль успешно обновлен');
    }
}
