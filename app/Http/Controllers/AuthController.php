<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function storeRegistration(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'orcid_id' => ['required', 'regex:/^\d{4}-\d{4}-\d{4}-[\dX]{4}$/i', 'unique:users,orcid_id'],
            'password' => ['required', 'confirmed', 'min:8'],
            'is_teacher' => ['nullable', 'boolean'],
        ], [
            'orcid_id.regex' => 'Use a valid ORCID iD such as 0000-0002-1825-0097.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'orcid_id' => strtoupper($validated['orcid_id']),
            'password' => Hash::make($validated['password']),
            'is_teacher' => (bool) ($validated['is_teacher'] ?? false),
            'trial_ends_at' => now()->addDays(7),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Those credentials could not be verified.']);
        }

        if (blank(Auth::user()?->orcid_id)) {
            Auth::logout();

            return back()->withErrors(['email' => 'This account needs an ORCID iD before it can access the research workspace.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
