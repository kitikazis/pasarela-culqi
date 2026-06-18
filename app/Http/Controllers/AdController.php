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
            'ad_id'   => $ad->id,
            'credits' => $user->fresh()->publish_credits,
        ], 201);
    }

    /**
     * Lista pública de anuncios activos.
     * Los DESTACADOS (featured vigente) aparecen primero.
     *
     * Los campos de texto del usuario (text/dep/prov/dist) se devuelven
     * HTML-escapados con e() para prevenir XSS almacenado: el frontend los
     * inserta con innerHTML. NO volver a escaparlos en el front (doble-encode).
     */
    public function index(): JsonResponse
    {
        $ads = Ad::where('status', 'active')
            ->orderByRaw('CASE WHEN featured_until IS NOT NULL AND featured_until > NOW() THEN 0 ELSE 1 END')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn (Ad $ad) => [
                'id'       => $ad->id,
                'cat'      => $ad->category,
                'text'     => e($ad->description),
                'dep'      => $ad->coverage === 'nacional' ? 'Nacional' : e($ad->department),
                'prov'     => e($ad->province),
                'dist'     => e($ad->district),
                'phone'    => $ad->phone,
                'date'     => $ad->created_at?->toDateString(),
                'featured' => $ad->isFeatured(),
            ]);

        return response()->json($ads);
    }

    /** Anuncios del usuario autenticado (para "Mis anuncios"). */
    public function mine(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['authenticated' => false], 401);
        }

        $ads = $user->ads()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Ad $ad) => [
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
            ]);

        return response()->json(['authenticated' => true, 'ads' => $ads]);
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

    /** Elimina (soft delete) un anuncio propio. */
    public function destroy(Ad $ad): JsonResponse
    {
        if (! $this->ownsAd($ad)) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $ad->delete();

        return response()->json(['success' => true]);
    }

    /** ¿El anuncio pertenece al usuario autenticado? */
    private function ownsAd(Ad $ad): bool
    {
        return Auth::check() && $ad->user_id === Auth::id();
    }
}

