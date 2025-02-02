<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRoleEnums;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\CartServices;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, CartServices $cartServices): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $route = '/';
        $user = Auth::user();

        if($user->hasRole([UserRoleEnums::Vendor, UserRoleEnums::Admin])){
            $cartServices->AddItemFromCookieToDatabase($user->id);
            return redirect()->intended(route('filament.admin.pages.dashboard'));
        } else {
            $route = route('dashboard', absolute: false);
        }
        $cartServices->AddItemFromCookieToDatabase($user->id);
        return redirect()->intended($route);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect(\route('dashboard'));
    }
}
