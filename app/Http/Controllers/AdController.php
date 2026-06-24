<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdRequest;
use App\Models\Ad;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdController extends Controller
{
    /** Crea un anuncio para el usuario autenticado. */
    public function store(StoreAdRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        // Publicar gasta 1 crédito. Crédito + anuncio en una transacción atómica:
        // el decremento condicional evita doble gasto / carreras.
        $ad = null;
        $ok = DB::transaction(function () use ($user, $data, &$ad): bool {
            $spent = User::whereKey($user->id)
                ->where('publish_credits', '>', 0)
                ->decrement('publish_credits');

            if (! $spent) {
                return false;   // sin créditos
            }

            $ad = $user->ads()->create([
                'category'    => $data['category'],
                'description' => $data['description'],
                'phone'       => $data['phone'],
                'coverage'    => $data['coverage'],
                'department'  => $data['coverage'] === 'nacional' ? null : ($data['department'] ?? null),
                'province'    => in_array($data['coverage'], ['provincial', 'distrital'], true) ? ($data['province'] ?? null) : null,
                'district'    => $data['coverage'] === 'distrital' ? ($data['district'] ?? null) : null,
                'status'      => 'active',
            ]);

            return true;
        });

        if (! $ok) {
            return response()->json([
                'success'   => false,
                'message'   => 'No te quedan créditos. Compra un plan para publicar.',
                'need_plan' => true,
            ], 402);
        }

        return response()->json([
            'success' => true,
            'message' => 'Anuncio publicado.',
            'publicacion_id' => $ad->id,
            'credits' => $user->fresh()->publish_credits,
        ], 201);
    }

    /** Cuántos anuncios por página devuelve la API pública. */
    private const PER_PAGE = 24;

    /** Días que un anuncio eliminado vive en la Papelera antes del borrado definitivo. */
    private const TRASH_DAYS = 30;

    /**
     * Lista pública de anuncios activos — FILTRADA y PAGINADA en el servidor.
     * Acepta query params: cat, dep, prov, dist, q (búsqueda), page.
     * Así la home solo recibe una página (no cientos), aunque haya miles en BD.
     *
     * Los campos de texto del usuario (text/dep/prov/dist) se devuelven
     * HTML-escapados con e() para prevenir XSS almacenado: el frontend los
     * inserta con innerHTML. NO volver a escaparlos en el front (doble-encode).
     */
    public function index(Request $request): JsonResponse
    {
        $cat  = $request->query('cat');
        $dep  = $request->query('dep');
        $prov = $request->query('prov');
        $dist = $request->query('dist');
        $term = trim((string) $request->query('q', ''));

        $ads = Ad::where('status', 'active')
            ->when($cat && $cat !== 'todos', fn ($qry) => $qry->where('category', $cat))
            // "Nacional" = anuncios de cobertura nacional; cualquier otro = ese departamento.
            ->when($dep === 'Nacional', fn ($qry) => $qry->where('coverage', 'nacional'))
            ->when($dep && $dep !== 'Nacional', fn ($qry) => $qry->where('department', $dep))
            ->when($prov, fn ($qry) => $qry->where('province', $prov))
            ->when($dist, fn ($qry) => $qry->where('district', $dist))
            // Escapa los comodines del usuario (% _ \) para que no actúen como LIKE.
            ->when($term !== '', fn ($qry) => $qry->where('description', 'like', '%'.addcslashes($term, '%_\\').'%'))
            ->orderByRaw('CASE WHEN featured_until IS NOT NULL AND featured_until > NOW() THEN 0 ELSE 1 END')
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE);

        return response()->json([
            'data' => collect($ads->items())->map(fn (Ad $ad) => [
                'id'       => $ad->id,
                'cat'      => $ad->category,
                'text'     => e($ad->description),
                'dep'      => $ad->coverage === 'nacional' ? 'Nacional' : e($ad->department),
                'prov'     => e($ad->province),
                'dist'     => e($ad->district),
                'phone'    => $ad->phone,
                'date'     => $ad->created_at?->toDateString(),
                'featured' => $ad->isFeatured(),
            ]),
            'page'  => $ads->currentPage(),
            'pages' => $ads->lastPage(),
            'total' => $ads->total(),
        ]);
    }

    /**
     * Anuncios del usuario autenticado (para "Mis anuncios") — PAGINADO.
     * Acepta query params: status (activo|inactivo|todos), page.
     * Devuelve también los conteos (total/activos/inactivos) calculados en BD,
     * para no traer todos los anuncios solo para contar.
     */
    public function mine(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['authenticated' => false], 401);
        }

        // Conteos en BD (no se descargan los anuncios para contarlos).
        $total     = $user->ads()->count();
        $activos   = $user->ads()->where('status', 'active')->count();
        $inactivos = $total - $activos;

        $status = $request->query('status');
        $ads = $user->ads()
            ->when($status === 'activo', fn ($qry) => $qry->where('status', 'active'))
            ->when($status === 'inactivo', fn ($qry) => $qry->where('status', 'inactive'))
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE);

        return response()->json([
            'authenticated' => true,
            'ads' => collect($ads->items())->map(fn (Ad $ad) => [
                'id'       => $ad->id,
                'cat'      => $ad->category,
                'text'     => e($ad->description),
                'dep'      => $ad->coverage === 'nacional' ? 'Nacional' : e($ad->department),
                'prov'     => e($ad->province),
                'dist'     => e($ad->district),
                'phone'    => $ad->phone,
                'date'     => $ad->created_at?->toDateString(),
                'status'   => $ad->status === 'active' ? 'activo' : 'inactivo',
                'views'    => $ad->views,
                'featured' => $ad->isFeatured(),
            ]),
            'page'   => $ads->currentPage(),
            'pages'  => $ads->lastPage(),
            'counts' => ['total' => $total, 'activos' => $activos, 'inactivos' => $inactivos],
        ]);
    }

    /** Activa o desactiva un anuncio propio. */
    public function update(Request $request, Ad $ad): JsonResponse
    {
        if (! $this->ownsAd($ad)) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $ad->update(['status' => $validated['status']]);

        return response()->json(['success' => true, 'status' => $ad->status]);
    }

    /**
     * Elimina un anuncio propio. NO se borra de verdad: es un soft delete
     * (se llena deleted_at). Queda en la Papelera 30 días y luego se purga.
     */
    public function destroy(Ad $ad): JsonResponse
    {
        if (! $this->ownsAd($ad)) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $ad->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Papelera: anuncios que el usuario eliminó en los últimos 30 días
     * (recuperables). Paginado, como las demás listas.
     */
    public function trashed(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['authenticated' => false], 401);
        }

        $ads = $user->ads()
            ->onlyTrashed()
            ->where('deleted_at', '>=', now()->subDays(self::TRASH_DAYS))
            ->orderByDesc('deleted_at')
            ->paginate(self::PER_PAGE);

        return response()->json([
            'authenticated' => true,
            'ads' => collect($ads->items())->map(fn (Ad $ad) => [
                'id'     => $ad->id,
                'cat'    => $ad->category,
                'text'   => e($ad->description),
                'dep'    => $ad->coverage === 'nacional' ? 'Nacional' : e($ad->department),
                'prov'   => e($ad->province),
                'dist'   => e($ad->district),
                'phone'  => $ad->phone,
                'date'   => $ad->created_at?->toDateString(),
                // Días que faltan para el borrado definitivo.
                'expira' => max(0, self::TRASH_DAYS - (int) $ad->deleted_at->diffInDays(now())),
            ]),
            'page'  => $ads->currentPage(),
            'pages' => $ads->lastPage(),
            'total' => $ads->total(),
        ]);
    }

    /** Restaura un anuncio que el usuario había eliminado (mientras siga en la Papelera). */
    public function restore(int $id): JsonResponse
    {
        $ad = Ad::withTrashed()->find($id);
        if (! $ad || ! $this->ownsAd($ad)) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $ad->restore();

        return response()->json(['success' => true]);
    }

    /** ¿El anuncio pertenece al usuario autenticado? */
    private function ownsAd(Ad $ad): bool
    {
        return Auth::check() && $ad->user_id === Auth::id();
    }
}

