<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'login_id' => 'required|string',
            'password' => 'required',
        ]);

        // rememberの値を取得し、Auth::attemptの第2引数に渡す
        $remember = $request->has('remember');

        if (!Auth::attempt($validated, $remember)) {
            return redirect()->route('login')->withErrors(['Invalid credentials']);
        }

        return redirect()->route('challenges.index')->with('success', 'Login successful');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'login_id' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-z0-9@._+\-]+$/',
                'unique:users,login_id',
            ],
            'password' => 'required|string|min:8',
        ]);

        \App\Models\User::create([
            'name' => $validated['name'],
            'login_id' => $validated['login_id'],
            'password' => bcrypt($validated['password']),
        ]);

        return redirect()->route('login')->with('success', 'Registration successful. Please log in.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully');
    }
}
