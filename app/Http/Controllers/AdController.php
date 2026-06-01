<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\JsonResponse;

class AdController extends Controller
{
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
                'dep'      => $ad->department,
                'prov'     => $ad->province,
                'dist'     => $ad->district,
                'phone'    => $ad->phone,
                'date'     => $ad->created_at?->toDateString(),
                'featured' => $ad->isFeatured(),
            ]);

        return response()->json($ads);
    }
}
