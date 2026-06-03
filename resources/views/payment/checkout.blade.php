<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Planes — {{ config('app.name') }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root { --brand:#0d6efd; --brand-dark:#0b5ed7; --line:#e6e9ef; --ink:#1a1f36; --muted:#6b7280; }
        * { box-sizing:border-box; font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif; }
        body { background:#eef1f6; margin:0; padding:2.5rem 1rem; color:var(--ink); }
        .wrap { max-width:960px; margin:0 auto; }
        .head { text-align:center; margin-bottom:2rem; }
        .head h1 { font-size:1.8rem; margin:0 0 .4rem; }
        .head p { color:var(--muted); margin:0; }
        .back { display:inline-flex; align-items:center; gap:.4rem; color:var(--brand);
                text-decoration:none; font-size:.9rem; margin-bottom:1rem; }

        .plans { display:grid; grid-template-columns:1fr; gap:1.25rem; }
        @media (min-width:768px){ .plans { grid-template-columns:repeat(3,1fr); } }

        .plan { background:#fff; border:2px solid var(--line); border-radius:16px; padding:1.5rem;
                cursor:pointer; transition:all .15s ease; position:relative; display:flex; flex-direction:column; }
        .plan:hover { border-color:#b9c4d4; transform:translateY(-2px); }
        .plan.selected { border-color:var(--brand); box-shadow:0 8px 24px rgba(13,110,253,.15); }
        .plan.popular { border-color:var(--brand); }
        .tag { position:absolute; top:-12px; left:50%; transform:translateX(-50%);
               background:var(--brand); color:#fff; font-size:.7rem; font-weight:700;
               padding:.25rem .75rem; border-radius:20px; letter-spacing:.03em; }
        .plan h3 { margin:0 0 .25rem; font-size:1.15rem; }
        .plan .desc { color:var(--muted); font-size:.85rem; margin:0 0 1rem; min-height:2.4em; }
        .price { font-size:2rem; font-weight:800; color:var(--ink); }
        .price small { font-size:.85rem; font-weight:500; color:var(--muted); }
        .features { list-style:none; padding:0; margin:1rem 0 0; flex:1; }
        .features li { font-size:.85rem; color:#3c4043; padding:.3rem 0; display:flex; gap:.5rem; align-items:flex-start; }
        .features li i { color:#34a853; margin-top:.2rem; }
        .pick { margin-top:1.25rem; text-align:center; font-weight:600; font-size:.85rem;
                color:var(--brand); border:1px dashed var(--brand); border-radius:8px; padding:.5rem; }
        .plan.selected .pick { background:var(--brand); color:#fff; border-style:solid; }

        .checkout { background:#fff; border-radius:16px; box-shadow:0 6px 28px rgba(0,0,0,.08);
                    padding:1.75rem; margin-top:1.75rem; max-width:480px; margin-left:auto; margin-right:auto; }
        .checkout h2 { font-size:1.1rem; margin:0 0 1rem; }
        label { display:block; font-size:.78rem; font-weight:600; color:#3c4043; margin:.7rem 0 .3rem; }
        input { width:100%; padding:.6rem .7rem; border:1px solid #d9dee5; border-radius:8px; font-size:.95rem; }
        input:focus { outline:none; border-color:var(--brand); box-shadow:0 0 0 3px rgba(13,110,253,.15); }
        .row { display:flex; gap:.6rem; } .row>div { flex:1; }
        button { width:100%; border:none; border-radius:10px; padding:.95rem; font-size:1rem;
                 font-weight:700; cursor:pointer; margin-top:1.25rem; background:var(--brand); color:#fff; }
        button:hover { background:var(--brand-dark); }
        button:disabled { opacity:.55; cursor:not-allowed; }
        .result { margin-top:1rem; padding:1rem; border-radius:10px; text-align:center; display:none; font-size:.92rem; }
        .result.show { display:block; }
        .result.ok { background:#e7f6ec; color:#0f7a37; border:1px solid #34a853; }
        .result.err { background:#fdecea; color:#b3261e; border:1px solid #ea4335; }
        .secure { text-align:center; font-size:.76rem; color:#9aa1ad; margin-top:1rem; }

        /* ── Responsive móvil ── */
        @media (max-width: 575.98px) {
            body { padding:1rem .8rem; }
            .head h1 { font-size:1.45rem; }
            .plan { padding:1.2rem; }
            .price { font-size:1.7rem; }
            .checkout { padding:1.25rem; margin-top:1.25rem; }
            /* Nombres y Apellidos apilados (no lado a lado) */
            .row { flex-direction:column; gap:0; }
            /* Botón de pago SIEMPRE visible en móvil */
            #btnPay {
                position:sticky;
                bottom:10px;
                box-shadow:0 6px 22px rgba(13,110,253,.4);
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <a class="back" href="/"><i class="fa-solid fa-arrow-left"></i> Volver al inicio</a>

        <div class="head">
            <h1>Destaca tu anuncio</h1>
            <p>Elige un plan y aparece en los primeros resultados.</p>
        </div>

        {{-- ── Planes (precios desde el backend) ── --}}
        <div class="plans">
            @foreach (config('plans') as $id => $plan)
                <div class="plan {{ ($plan['popular'] ?? false) ? 'popular' : '' }}" data-plan="{{ $id }}" data-amount="{{ $plan['amount'] }}">
                    @if ($plan['popular'] ?? false)
                        <span class="tag">MÁS POPULAR</span>
                    @endif
                    <h3>{{ $plan['name'] }}</h3>
                    <p class="desc">{{ $plan['description'] }}</p>
                    <div class="price">S/ {{ number_format($plan['amount'] / 100, 2) }}</div>
                    <ul class="features">
                        @foreach ($plan['features'] as $f)
                            <li><i class="fa-solid fa-check"></i> {{ $f }}</li>
                        @endforeach
                    </ul>
                    <div class="pick">Seleccionar</div>
                </div>
            @endforeach
        </div>

        {{-- ── Checkout ── --}}
        <div class="checkout">
            <h2>Tus datos</h2>
            <div class="row">
                <div>
                    <label for="firstName">Nombres</label>
                    <input type="text" id="firstName" value="Juan" autocomplete="given-name">
                </div>
                <div>
                    <label for="lastName">Apellidos</label>
                    <input type="text" id="lastName" value="Pérez" autocomplete="family-name">
                </div>
            </div>
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" value="cliente@test.com" autocomplete="email">
            <label for="phone">Celular</label>
            <input type="tel" id="phone" value="999888777" maxlength="9" inputmode="numeric">

            <button id="btnPay" type="button" disabled>Selecciona un plan</button>

            <div class="result" id="result"></div>
            <p class="secure">🔒 Pago seguro con Culqi. Tus datos de tarjeta nunca pasan por este sitio.</p>
        </div>
    </div>

    {{-- SOLO CDN oficial de Culqi --}}
    <script src="https://checkout.culqi.com/js/v4"></script>
    <script>
        const TITLE        = @json(config('app.name'));
        const CSRF         = document.querySelector('meta[name="csrf-token"]').content;
        const URL_CARGO    = @json(route('pago.cargo'));
        const URL_ORDEN    = @json(route('pago.orden'));
        const URL_CONFIRMAR = @json(route('pago.orden.confirmar'));

        let selectedPlan   = null;
        let selectedAmount = 0;   // céntimos (solo para mostrar en el widget)
        let procesando     = false; // guard contra doble pago / doble disparo del callback

        Culqi.publicKey = @json(config('culqi.public_key'));

        // ── Selección de plan ──
        document.querySelectorAll('.plan').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.plan').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                selectedPlan   = card.dataset.plan;
                selectedAmount = parseInt(card.dataset.amount);
                const btn = document.getElementById('btnPay');
                btn.disabled = false;
                btn.textContent = 'Pagar S/ ' + (selectedAmount / 100).toFixed(2);
            });
        });

        function configurarCheckout(orderId = null) {
            
            const settings = { title: TITLE, currency: 'PEN', amount: selectedAmount };
            if (orderId) settings.order = orderId;
            Culqi.settings(settings);
            Culqi.options({
                lang: 'es',
                installments: true,
                paymentMethods: {
                    tarjeta: true, yape: true, billetera: true,
                    bancaMovil: true, agente: true, cuotealo: true,
                },
                style: {
                    bannerColor: '#0d6efd',
                    buttonBackground: '#0d6efd',
                    menuColor: '#0d6efd',
                    linksColor: '#0d6efd',
                    buttonText: 'Pagar',   // Culqi le agrega el monto solo (ej: "Pagar S/ 1.00")
                    buttonTextColor: '#ffffff',
                    priceColor: '#1a1f36',
                },
            });
        }

        document.getElementById('btnPay').addEventListener('click', abrirCheckout);

        async function abrirCheckout() {
            if (!selectedPlan) return;
            procesando = false;   // nuevo intento de pago
            const btn = document.getElementById('btnPay');
            btn.disabled = true; btn.textContent = 'Preparando...';

            // Creamos una orden (habilita PagoEfectivo/Cuotéalo). Enviamos el PLAN, no el monto.
            try {
                const res = await fetch(URL_ORDEN, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({
                        plan: selectedPlan,
                        email: val('email'), first_name: val('firstName'),
                        last_name: val('lastName'), phone_number: val('phone'),
                    }),
                });
                const data = await res.json();
                configurarCheckout(data.success ? data.order_id : null);
            } catch (e) {
                configurarCheckout(null);
            }

            btn.disabled = false; btn.textContent = 'Pagar S/ ' + (selectedAmount / 100).toFixed(2);
            Culqi.open();
        }

        // ── Callback de Culqi v4 ──
        // IMPORTANTE: si hay ORDEN, ELLA es el pago. NO se carga el token aparte
        // (eso causaba el doble cobro). Solo se carga token cuando NO hubo orden.
        window.culqi = function () {
            if (procesando) return;           // evita doble disparo del callback
            procesando = true;

            if (Culqi.order) {
                confirmarOrden(Culqi.order.id);                 // la orden es el pago
            } else if (Culqi.token) {
                enviarCargo(Culqi.token.id, Culqi.token.email); // fallback: sin orden
            } else if (Culqi.error) {
                procesando = false;
                mostrar('err', Culqi.error.user_message || 'No se pudo procesar el pago.');
            } else {
                procesando = false;
            }
        };

        // Verifica el estado real de la orden en el backend (un solo pago)
        async function confirmarOrden(orderId) {
            mostrar('ok', 'Verificando pago...');
            try {
                const res = await fetch(URL_CONFIRMAR, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ order_id: orderId }),
                });
                const data = await res.json();
                if (data.success && data.paid) {
                    mostrar('ok', '✅ Pago confirmado. ¡Gracias! (orden ' + orderId + ')');
                } else if (data.success) {
                    mostrar('ok', '🧾 Orden ' + orderId + ' generada. Completa el pago con las instrucciones; lo confirmaremos automáticamente.');
                } else {
                    mostrar('err', '❌ ' + (data.message || 'No se pudo verificar el pago.'));
                }
            } catch (e) {
                mostrar('err', '❌ Error de conexión. Intenta nuevamente.');
            } finally {
                procesando = false;
            }
        }

        // Fallback: cargo por token cuando NO se pudo crear la orden
        async function enviarCargo(tokenId, email) {
            mostrar('ok', 'Procesando pago...');
            try {
                const res = await fetch(URL_CARGO, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({
                        token: tokenId,
                        plan: selectedPlan,          // el precio lo pone el backend
                        email: email || val('email'),
                    }),
                });
                const data = await res.json();
                data.success
                    ? mostrar('ok', '✅ ' + data.message + ' (cargo ' + data.charge_id + ')')
                    : mostrar('err', '❌ ' + (data.message || 'No se pudo procesar el pago.'));
            } catch (e) {
                mostrar('err', '❌ Error de conexión. Intenta nuevamente.');
            } finally {
                procesando = false;
            }
        }

        function val(id) { return document.getElementById(id).value.trim(); }
        function mostrar(tipo, msg) {
            const box = document.getElementById('result');
            box.className = 'result show ' + tipo;
            box.textContent = msg;
        }
    </script>
</body>
</html>
