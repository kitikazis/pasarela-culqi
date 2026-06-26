<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ad extends Model
{
    use SoftDeletes;

    /**
     * Tabla en BD. Se llama "publicaciones" (no "ads") por consistencia con la
     * marca y la ruta pública /publicaciones; el modelo sigue siendo Ad.
     */
    protected $table = 'publicaciones';

    /** Largo máximo de la descripción (fuente única de verdad: validación y formulario). */
    public const MAX_DESCRIPTION = 144;

    protected $fillable = [
        'user_id',
        'categoria',
        'descripcion',
        'telefono',
        'cobertura',
        'departamento',
        'provincia',
        'distrito',
        'estado',
        'destacado_hasta',
        'vistas',
    ];

    protected $casts = [
        'destacado_hasta' => 'datetime',
        'vistas'          => 'integer',
    ];

    /**
     * Mantiene `deleted_at` en sincronía con el estado:
     *  - al pasar a "borrado" → registra la hora exacta del borrado (va a la Papelera).
     *  - al dejar de estar "borrado" → se restaura (limpia deleted_at).
     * Solo reacciona cuando `estado` cambia, para no interferir con otros guardados.
     */
    protected static function booted(): void
    {
        static::saving(function (Ad $ad): void {
            if (! $ad->isDirty('estado')) {
                return;
            }
            if ($ad->estado === 'borrado') {
                $ad->deleted_at = now();
            } elseif ($ad->getOriginal('estado') === 'borrado') {
                $ad->deleted_at = null;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** ¿El anuncio está destacado ahora mismo? */
    public function isFeatured(): bool
    {
        return $this->destacado_hasta !== null && $this->destacado_hasta->isFuture();
    }

    /** Destaca el anuncio por N días (acumula si ya estaba destacado). */
    public function feature(int $days): void
    {
        $base = $this->isFeatured() ? $this->destacado_hasta : now();
        $this->update(['destacado_hasta' => $base->copy()->addDays($days)]);
    }

    /** Scope: solo anuncios destacados vigentes. */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->whereNotNull('destacado_hasta')->where('destacado_hasta', '>', now());
    }
}
