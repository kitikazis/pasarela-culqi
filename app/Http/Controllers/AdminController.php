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

        // Vista previa: últimos 5 de cada uno (el detalle completo va en su página).
        $users = User::withCount('ads')->latest()->take(5)
            ->get(['id', 'name', 'email', 'avatar', 'provider', 'publish_credits', 'created_at']);

        $ads = Ad::withTrashed()->with('user:id,name')->latest()->take(5)
            ->get(['id', 'user_id', 'categoria', 'descripcion', 'estado', 'vistas', 'created_at', 'deleted_at']);

        $transactions = Transaction::with('user:id,name')->latest()->take(5)
            ->get(['id', 'user_id', 'order_number', 'charge_id', 'payment_method',
                   'amount', 'currency', 'status', 'customer_name', 'created_at']);

        return view('admin.dashboard', compact('stats', 'users', 'ads', 'transactions'));
    }

    /** Página completa de usuarios (buscador + filtro por proveedor + paginación). */
    public function users(Request $request)
    {
        $q        = trim((string) $request->query('q', ''));
        $provider = $request->query('provider');

        $users = User::withCount('ads')
            ->when($q !== '', fn ($query) => $query->where(function ($sub) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $sub->where('name', 'like', $like)->orWhere('email', 'like', $like);
            }))
            ->when($provider, fn ($query) => $query->where('provider', $provider))
            ->latest()
            ->paginate(20)
            ->appends($request->except('partial', 'page'));

        if ($request->boolean('partial')) {
            return view('admin.partials.users-results', compact('users', 'q', 'provider'));
        }

        return view('admin.users', compact('users', 'q', 'provider'));
    }

    /** Página completa de anuncios (buscador + filtros por estado/categoría + paginación). */
    public function ads(Request $request)
    {
        $q         = trim((string) $request->query('q', ''));
        $estado    = $request->query('estado');     // active | inactive | borrado
        $categoria = $request->query('categoria');

        $query = Ad::with('user:id,name');

        // Estado: "borrado" = en Papelera; activo/inactivo = no borrados; sin filtro = todos.
        if ($estado === 'borrado') {
            $query->onlyTrashed();
        } elseif (in_array($estado, ['active', 'inactive'], true)) {
            $query->where('estado', $estado);
        } else {
            $query->withTrashed();
        }

        $ads = $query
            ->when($q !== '', fn ($qb) => $qb->where('descripcion', 'like', '%' . addcslashes($q, '%_\\') . '%'))
            ->when($categoria, fn ($qb) => $qb->where('categoria', $categoria))
            ->latest()
            ->paginate(20, [
                'id', 'user_id', 'categoria', 'descripcion', 'estado', 'vistas', 'created_at', 'deleted_at',
            ])
            ->appends($request->except('partial', 'page'));

        if ($request->boolean('partial')) {
            return view('admin.partials.ads-results', compact('ads', 'q', 'estado', 'categoria'));
        }

        return view('admin.ads', compact('ads', 'q', 'estado', 'categoria'));
    }

    /** Página completa de transacciones (buscador + filtros por estado/método + paginación). */
    public function transactions(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        $method = $request->query('method');

        $transactions = Transaction::with('user:id,name')
            ->when($q !== '', fn ($query) => $query->where(function ($sub) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $sub->where('order_number', 'like', $like)
                    ->orWhere('charge_id', 'like', $like)
                    ->orWhere('customer_name', 'like', $like);
            }))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($method, fn ($query) => $query->where('payment_method', $method))
            ->latest()
            ->paginate(20, [
                'id', 'user_id', 'order_number', 'charge_id', 'payment_method',
                'amount', 'currency', 'status', 'customer_name', 'created_at',
            ])
            ->appends($request->except('partial', 'page'));

        // Filtrado en vivo (AJAX): devuelve solo los resultados, sin el layout.
        if ($request->boolean('partial')) {
            return view('admin.partials.transactions-results', compact('transactions', 'q', 'status', 'method'));
        }

        return view('admin.transactions', compact('transactions', 'q', 'status', 'method'));
    }
}
