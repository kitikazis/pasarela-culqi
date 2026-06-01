<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

/**
 * Login social vía OAuth (Google / Microsoft) con Laravel Socialite.
 * No maneja contraseñas: la identidad la valida el proveedor externo.
 */
class AuthController extends Controller
{
    private const PROVIDERS = ['google', 'microsoft'];

    /** Redirige al proveedor para autenticarse. */
    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        return Socialite::driver($provider)->redirect();
    }

    /** Recibe el callback del proveedor, crea/actualiza el usuario e inicia sesión. */
    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        try {
            $oauthUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            Log::warning('OAuth callback falló', ['provider' => $provider, 'error' => $e->getMessage()]);

            return redirect('/')->with('error', 'No se pudo iniciar sesión. Intenta nuevamente.');
        }

        if (! $oauthUser->getEmail()) {
            return redirect('/')->with('error', 'Tu cuenta no entregó un correo válido.');
        }

        // Vincula por email: si ya existe, actualiza; si no, lo crea.
        $user = User::updateOrCreate(
            ['email' => $oauthUser->getEmail()],
            [
                'name'              => $oauthUser->getName() ?: ($oauthUser->getNickname() ?: 'Usuario'),
                'avatar'            => $oauthUser->getAvatar(),
                'provider'          => $provider,
                'provider_id'       => $oauthUser->getId(),
                'email_verified_at' => now(),
            ],
        );

        Auth::login($user, remember: true);

        return redirect()->intended('/mis-anuncios.html');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
