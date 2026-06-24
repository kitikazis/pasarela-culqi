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
        'category',
        'description',
        'phone',
        'coverage',
        'department',
        'province',
        'district',
        'status',
        'featured_until',
        'views',
    ];

    protected $casts = [
        'featured_until' => 'datetime',
        'views'          => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** ¿El anuncio está destacado ahora mismo? */
    public function isFeatured(): bool
    {
        return $this->featured_until !== null && $this->featured_until->isFuture();
    }

    /** Destaca el anuncio por N días (acumula si ya estaba destacado). */
    public function feature(int $days): void
    {
        $base = $this->isFeatured() ? $this->featured_until : now();
        $this->update(['featured_until' => $base->copy()->addDays($days)]);
    }

    /** Scope: solo anuncios destacados vigentes. */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->whereNotNull('featured_until')->where('featured_until', '>', now());
    }
}
