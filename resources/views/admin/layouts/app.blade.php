<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'Admin - Anuncialo')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#FF7A00',
                            light: '#FFD59A',
                            dark: '#E65A00'
                        },
                        sun: '#FFCB2B'
                    }
                }
            }
        }
    </script>

    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* Minimal page-safe background */
        body { background-color: #f7f7fa; }
    </style>
</head>
<body class="min-h-screen font-sans text-gray-800">
    <div class="min-h-screen flex">
        @yield('sidebar')
        <div class="flex-1 p-6">
            <header class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-md bg-brand text-white flex items-center justify-center font-bold">A</div>
                    <h1 class="text-lg font-semibold">Anuncialo - Panel de Administración</h1>
                </div>
                <div>
                    @if(session('admin_name'))
                        <form method="POST" action="{{ route('admin.logout') }}">@csrf
                            <button class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200">Cerrar sesión</button>
                        </form>
                    @endif
                </div>
            </header>

            @yield('content')
        </div>
    </div>

    <script>lucide.createIcons();</script>

    {{-- Filtrado en vivo (AJAX) para las páginas de listado del panel.
         Se activa en cualquier <form data-live-filter action=… data-results="ID">. --}}
    <script>
        (function () {
            const form = document.querySelector('form[data-live-filter]');
            if (! form) return;
            const results  = document.getElementById(form.dataset.results);
            const loading  = form.dataset.loading ? document.getElementById(form.dataset.loading) : null;
            const clearBtn = form.querySelector('[data-clear]');
            const base     = form.getAttribute('action');
            if (! results) return;
            let timer = null, seq = 0;

            function buildUrl() {
                const params = new URLSearchParams();
                new FormData(form).forEach((v, k) => { if (String(v).trim() !== '') params.set(k, v); });
                const qs = params.toString();
                if (clearBtn) clearBtn.classList.toggle('hidden', qs === '');
                return base + (qs ? '?' + qs : '');
            }

            async function load(url, push = true) {
                const mySeq = ++seq;
                if (loading) loading.classList.remove('hidden');
                const sep = url.includes('?') ? '&' : '?';
                try {
                    const res = await fetch(url + sep + 'partial=1', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    const html = await res.text();
                    if (mySeq !== seq) return;          // ignora respuestas viejas
                    results.innerHTML = html;
                    if (window.lucide) lucide.createIcons();
                    if (push) history.replaceState(null, '', url);
                } catch (e) {
                    /* silencioso: deja los resultados actuales */
                } finally {
                    if (mySeq === seq && loading) loading.classList.add('hidden');
                }
            }

            function refresh() { load(buildUrl()); }

            const q = form.querySelector('[name="q"]');
            if (q) q.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(refresh, 300); });
            form.querySelectorAll('select').forEach(s => s.addEventListener('change', refresh));
            form.addEventListener('submit', (e) => { e.preventDefault(); clearTimeout(timer); refresh(); });
            if (clearBtn) clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (q) q.value = '';
                form.querySelectorAll('select').forEach(s => s.value = '');
                refresh();
            });
            // Paginación dentro de los resultados → también por AJAX.
            results.addEventListener('click', (e) => {
                const a = e.target.closest('a[href]');
                if (! a) return;
                const href = a.getAttribute('href');
                if (! href || href === '#') return;
                e.preventDefault();
                load(href);
            });
        })();
    </script>
</body>
</html>
