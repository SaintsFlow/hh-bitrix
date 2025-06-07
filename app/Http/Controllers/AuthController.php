<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Client;
use App\Models\Employee;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        // Попробуем авторизовать через User (супер-админ)
        if (Auth::guard('web')->attempt($credentials)) {
            $user = Auth::user();
            $user->update(['last_login_at' => now()]);

            if ($user->hasRole('super-admin')) {
                return redirect()->route('super-admin.dashboard');
            }
        }

        // Попробуем авторизовать через Client
        $client = Client::where('email', $request->email)->first();
        if ($client && Hash::check($request->password, $client->password)) {
            Auth::guard('client')->login($client);
            $client->update(['last_login_at' => now()]);
            return redirect()->route('client.dashboard');
        }

        // Попробуем авторизовать через Employee
        $employee = Employee::where('email', $request->email)->first();
        if ($employee && Hash::check($request->password, $employee->password)) {
            Auth::guard('employee')->login($employee);
            $employee->update(['last_login_at' => now()]);
            return redirect()->route('employee.dashboard');
        }

        return back()->withErrors([
            'email' => 'Неверные учетные данные.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Auth::guard('client')->logout();
        Auth::guard('employee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
