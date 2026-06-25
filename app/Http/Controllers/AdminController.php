<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Panel de administración (/admin).
 *
 * Login simple usuario/contraseña de PRUEBA contra config('admin_panel')
 * (admin/123 por defecto). El acceso al dashboard lo guarda el middleware
 * 'admin.panel' (EnsureAdminPanel), que exige la marca de sesión que pone login().
 *
 * Nota: esto es independiente del login OAuth de los usuarios y de ADMIN_EMAILS
 * (que protege las devoluciones). Para producción conviene migrar a OAuth + ADMIN_EMAILS.
 */
class AdminController extends Controller
{
    public function showLogin()
    {
        // Si ya inició sesión en el panel, directo al dashboard.
        if (session('admin_authenticated')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'usuario'  => 'required|string',
            'password' => 'required|string',
        ]);

        $expectedUser = (string) config('admin_panel.user');
        $expectedPass = (string) config('admin_panel.password');

        // Comparación en tiempo constante (evita ataques de temporización).
        $ok = hash_equals($expectedUser, $data['usuario'])
            && hash_equals($expectedPass, $data['password']);

        if (! $ok) {
            return back()
                ->withErrors(['usuario' => 'Usuario o contraseña incorrectos.'])
                ->onlyInput('usuario');
        }

        // Regenera el ID de sesión al autenticar (anti session-fixation).
        $request->session()->regenerate();
        session(['admin_authenticated' => true, 'admin_name' => 'Administrador']);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['admin_authenticated', 'admin_name']);

        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        // Métricas detalladas. Los anuncios "borrado" están en la Papelera
        // (SoftDeletes): por eso se cuentan con onlyTrashed()/withTrashed().
        $stats = [
            'users'        => User::count(),
            'users_today'  => User::whereDate('created_at', today())->count(),
            'ads_total'    => Ad::withTrashed()->count(),
            'ads_active'   => Ad::where('estado', 'active')->count(),
            'ads_inactive' => Ad::where('estado', 'inactive')->count(),
            'ads_borrado'  => Ad::onlyTrashed()->count(),
            'ads_today'    => Ad::withTrashed()->whereDate('created_at', today())->count(),
            'transactions' => Transaction::count(),
            'paid'         => Transaction::where('status', 'paid')->count(),
            'pending'      => Transaction::where('status', 'pending')->count(),
            // Ingresos confirmados, en céntimos (se divide /100 en la vista).
            'revenue'      => (int) Transaction::where('status', 'paid')->sum('amount'),
        ];

        // Últimos registros reales de la BD (con la cuenta de anuncios por usuario).
        $users = User::withCount('ads')->latest()->take(15)
            ->get(['id', 'name', 'email', 'avatar', 'provider', 'publish_credits', 'created_at']);

        // Incluye los borrados (withTrashed) para verlos con su fecha de borrado.
        $ads = Ad::withTrashed()->with('user:id,name')->latest()->take(15)
            ->get(['id', 'user_id', 'categoria', 'descripcion', 'estado', 'vistas', 'created_at', 'deleted_at']);

        $transactions = Transaction::with('user:id,name')->latest()->take(15)
            ->get(['id', 'user_id', 'order_number', 'charge_id', 'payment_method',
                   'amount', 'currency', 'status', 'customer_name', 'created_at']);

        return view('admin.dashboard', compact('stats', 'users', 'ads', 'transactions'));
    }
}
