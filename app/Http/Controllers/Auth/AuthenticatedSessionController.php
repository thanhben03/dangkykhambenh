<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // $request->authenticate();

        // $request->session()->regenerate();

        // if (Auth::user()->user_type == 'admin') {
        //     return redirect()->intended(route('showPatient', absolute: false));
        // }

        // return redirect()->intended(route('dashboard', absolute: false));

        $data = $request->all();
        $user = User::query()->where('email', '=', $data['email'])->first();
        if (!$user) {
            return redirect()->back()->withErrors(['email' => 'Không tìm thấy tài khoản này.']);
        }

        if (!Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['password' => 'Mật khẩu không đúng.']);
        }

        Auth::login($user);

        if ($user->user_type == 'admin') {
            return redirect()->intended(route('showPatient', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
