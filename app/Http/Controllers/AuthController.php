<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        return view('login', [
            'error' => $request->session()->get('login_error'),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = DB::table('users')->where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            $request->session()->flash('login_error', 'Invalid credentials.');
            return redirect()->route('login');
        }

        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_name', $user->name);

        return redirect()->route('polls.index');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['user_id', 'user_name']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
