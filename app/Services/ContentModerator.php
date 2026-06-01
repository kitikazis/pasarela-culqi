<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Modera el texto de los anuncios.
 *
 * Capas:
 *   1) Lista local por categorías (groserías + servicios para adultos) — rápida, offline.
 *   2) Google Perspective API (opcional) — IA de toxicidad, si hay API key.
 *
 * Si la IA falla, se cae con gracia a la capa local (no bloquea por caídas de red).
 */
class ContentModerator
{
    /**
     * Revisa el texto. Devuelve null si está limpio, o un mensaje si se debe rechazar.
     */
    public function check(string $text): ?string
    {
        if (! config('moderation.enabled', true)) {
            return null;
        }

        $normalized = $this->normalize($text);

        if ($this->matchesAny($normalized, (array) config('moderation.profanity', []))) {
            return 'Tu anuncio contiene lenguaje no permitido. Por favor edítalo.';
        }

        if ($this->matchesAny($normalized, (array) config('moderation.adult', []))) {
            return 'Tu anuncio parece ofrecer servicios para adultos, lo cual no está permitido en esta plataforma.';
        }

        if ($this->perspectiveBlocks($text)) {
            return 'Tu anuncio fue marcado como contenido inapropiado. Por favor revísalo.';
        }

        return null;
    }

    public function isClean(string $text): bool
    {
        return $this->check($text) === null;
    }

    // ── Capa 1: lista local ─────────────────────────────────────

    private function matchesAny(string $normalizedText, array $entries): bool
    {
        foreach ($entries as $entry) {
            $term = $this->normalize($entry);
            if ($term === '') {
                continue;
            }

            // Frases (con espacio) → subcadena. Palabras sueltas → palabra completa.
            if (str_contains($term, ' ')) {
                if (str_contains($normalizedText, $term)) {
                    return true;
                }
            } elseif (preg_match('/\b' . preg_quote($term, '/') . '\b/u', $normalizedText)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        $text = strtr($text, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n',
        ]);

        // Leet básico
        $text = strtr($text, [
            '0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '@' => 'a', '$' => 's',
        ]);

        // Colapsar 3+ repeticiones (putaaaa → puta)
        $text = preg_replace('/(.)\1{2,}/u', '$1', $text);

        // Normalizar espacios
        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    // ── Capa 2: Google Perspective API (opcional) ──────────────

    private function perspectiveBlocks(string $text): bool
    {
        $cfg = config('moderation.perspective');

        if (empty($cfg['enabled']) || empty($cfg['api_key'])) {
            return false; // IA desactivada → no bloquea (la capa local ya actuó)
        }

        try {
            $requested = [];
            foreach ($cfg['attributes'] as $attr) {
                $requested[$attr] = (object) [];
            }

            $response = Http::timeout(8)->post(
                'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key=' . $cfg['api_key'],
                [
                    'comment'             => ['text' => $text],
                    'languages'           => [$cfg['language'] ?? 'es'],
                    'requestedAttributes' => $requested,
                ]
            );

            if (! $response->successful()) {
                Log::warning('Perspective API no disponible', ['status' => $response->status()]);
                return false; // falla seguro
            }

            $scores = $response->json('attributeScores', []);
            foreach ($scores as $attr => $data) {
                $value = $data['summaryScore']['value'] ?? 0;
                if ($value >= ($cfg['threshold'] ?? 0.85)) {
                    Log::info('Perspective bloqueó contenido', ['attr' => $attr, 'score' => $value]);
                    return true;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Perspective API error', ['message' => $e->getMessage()]);
            return false; // falla seguro
        }

        return false;
    }
}
