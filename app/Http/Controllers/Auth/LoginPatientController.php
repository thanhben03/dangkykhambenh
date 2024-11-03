<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginPatientController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.patient.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->all();
        $patient = Patient::query()->where('nic', '=', $data['cccd'])->first();
        if (!Hash::check($data['password'], $patient->password)) {
            return back()->withErrors(['password' => 'The provided credentials are incorrect.']);
        }
        Auth::guard('patient')->login($patient);
        session(['patient' => $patient]);
        return redirect()->route('patient.dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('patient')->logout();
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/patient/login');
    }
}
