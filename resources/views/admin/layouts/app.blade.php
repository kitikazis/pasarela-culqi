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
</body>
</html>
