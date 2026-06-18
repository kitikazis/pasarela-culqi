<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdRequest;
use App\Models\Ad;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    /** Crea un anuncio para el usuario autenticado. */
    public function store(StoreAdRequest $request): JsonResponse
    {
        $data = $request->validated();

        $ad = Auth::user()->ads()->create([
            'category'    => $data['category'],
            'description' => $data['description'],
            'phone'       => $data['phone'],
            'coverage'    => $data['coverage'],
            'department'  => $data['coverage'] === 'nacional' ? null : ($data['department'] ?? null),
            'province'    => in_array($data['coverage'], ['provincial', 'distrital'], true) ? ($data['province'] ?? null) : null,
            'district'    => $data['coverage'] === 'distrital' ? ($data['district'] ?? null) : null,
            'status'      => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Anuncio publicado.',
            'ad_id'   => $ad->id,
        ], 201);
    }

    /**
     * Lista pública de anuncios activos.
     * Los DESTACADOS (featured vigente) aparecen primero.
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
                'text'     => $ad->description,
                'dep'      => $ad->coverage === 'nacional' ? 'Nacional' : $ad->department,
                'prov'     => $ad->province,
                'dist'     => $ad->district,
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
                'text'     => $ad->description,
                'dep'      => $ad->coverage === 'nacional' ? 'Nacional' : $ad->department,
                'prov'     => $ad->province,
                'dist'     => $ad->district,
                'phone'    => $ad->phone,
                'date'     => $ad->created_at?->toDateString(),
                'status'   => $ad->status === 'active' ? 'activo' : 'inactivo',
                'views'    => $ad->views,
                'featured' => $ad->isFeatured(),
            ]);

        return response()->json(['authenticated' => true, 'ads' => $ads]);
    }
}

