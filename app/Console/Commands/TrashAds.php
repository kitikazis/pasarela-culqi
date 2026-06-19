<?php

namespace App\Console\Commands;

use App\Models\Ad;
use Illuminate\Console\Command;

/**
 * Control de la Papelera para el administrador (mientras no exista el panel web).
 *
 *   php artisan ads:trash                 → lista los anuncios eliminados
 *   php artisan ads:trash --user=x@y.com  → solo los de ese usuario
 *   php artisan ads:trash --restore=123   → restaura ese anuncio
 *   php artisan ads:trash --purge=123     → lo borra DEFINITIVAMENTE
 */
class TrashAds extends Command
{
    protected $signature = 'ads:trash
        {--user= : Filtra por el email del dueño}
        {--restore= : ID del anuncio a restaurar}
        {--purge= : ID del anuncio a borrar definitivamente}';

    protected $description = 'Administra la Papelera de anuncios (listar / restaurar / purgar).';

    public function handle(): int
    {
        // Restaurar un anuncio puntual.
        if ($id = $this->option('restore')) {
            $ad = Ad::withTrashed()->find($id);
            if (! $ad || ! $ad->trashed()) {
                $this->error("No hay un anuncio en la papelera con id {$id}.");

                return self::FAILURE;
            }
            $ad->restore();
            $this->info("Anuncio {$id} restaurado.");

            return self::SUCCESS;
        }

        // Borrar definitivamente un anuncio puntual.
        if ($id = $this->option('purge')) {
            $ad = Ad::withTrashed()->find($id);
            if (! $ad || ! $ad->trashed()) {
                $this->error("No hay un anuncio en la papelera con id {$id}.");

                return self::FAILURE;
            }
            $ad->forceDelete();
            $this->info("Anuncio {$id} borrado definitivamente.");

            return self::SUCCESS;
        }

        // Listar la papelera.
        $email = $this->option('user');
        $ads = Ad::onlyTrashed()
            ->when($email, fn ($q) => $q->whereHas('user', fn ($u) => $u->where('email', $email)))
            ->with('user:id,email')
            ->orderByDesc('deleted_at')
            ->limit(100)
            ->get();

        if ($ads->isEmpty()) {
            $this->info('La papelera está vacía.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Usuario', 'Descripción', 'Eliminado', 'Días rest.'],
            $ads->map(fn (Ad $ad) => [
                $ad->id,
                optional($ad->user)->email ?? '—',
                mb_strimwidth($ad->description, 0, 40, '…'),
                $ad->deleted_at->format('d/m/Y H:i'),
                max(0, 30 - (int) $ad->deleted_at->diffInDays(now())),
            ])->all()
        );

        $this->line('Total en papelera: '.$ads->count().' (máx. 100 mostrados).');

        return self::SUCCESS;
    }
}
