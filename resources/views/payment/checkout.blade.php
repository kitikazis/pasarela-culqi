<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Planes — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --brand:#FF7A00; --brand-dark:#E66900; --line:#E5E7EB; --ink:#1F2937; --muted:#6B7280; --success:#10B981; }
        * { box-sizing:border-box; font-family:'Inter',system-ui,-apple-system,Segoe UI,sans-serif; }
        body { background:#F9FAFB; margin:0; padding:2.5rem 1rem; color:var(--ink); -webkit-font-smoothing:antialiased; }
        .lucide { width:18px; height:18px; vertical-align:middle; flex-shrink:0; }
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
        .plan.selected { border-color:var(--brand); box-shadow:0 8px 24px rgba(255,122,0,.15); }
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
        .features li svg { color:var(--success); margin-top:.1rem; }
        .pick { margin-top:1.25rem; text-align:center; font-weight:600; font-size:.85rem;
                color:var(--brand); border:1px dashed var(--brand); border-radius:8px; padding:.5rem; }
        .plan.selected .pick { background:var(--brand); color:#fff; border-style:solid; }

        .checkout { background:#fff; border-radius:16px; box-shadow:0 6px 28px rgba(0,0,0,.08);
                    padding:1.75rem; margin-top:1.75rem; max-width:480px; margin-left:auto; margin-right:auto; }
        .checkout h2 { font-size:1.1rem; margin:0 0 1rem; }
        label { display:block; font-size:.78rem; font-weight:600; color:#3c4043; margin:.7rem 0 .3rem; }
        input { width:100%; padding:.6rem .7rem; border:1px solid #d9dee5; border-radius:8px; font-size:.95rem; }
        input:focus { outline:none; border-color:var(--brand); box-shadow:0 0 0 3px rgba(255,122,0,.15); }
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

        /* Resumen compacto de "Tus datos" cuando hay sesión */
        .identity-summary {
            display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem;
            background:#f5f7fb; border:1px solid var(--line); border-radius:10px;
            padding:.75rem .9rem; margin-bottom:.4rem;
        }
        .identity-summary a { color:var(--brand); font-size:.82rem; text-decoration:none; white-space:nowrap; }
        .identity-summary a:hover { text-decoration:underline; }

        /* ── Overlay "procesando pago" (bloquea la pantalla, evita pagar 2 veces) ── */
        .paying-overlay {
            position:fixed; inset:0; z-index:9999;
            background:rgba(17,24,39,.55); backdrop-filter:blur(2px);
            display:flex; flex-direction:column; align-items:center; justify-content:center;
            gap:1.1rem; color:#fff; text-align:center;
            opacity:0; visibility:hidden; transition:opacity .2s ease;
        }
        .paying-overlay.show { opacity:1; visibility:visible; }
        .paying-overlay .spinner {
            width:56px; height:56px; border-radius:50%;
            border:5px solid rgba(255,255,255,.3); border-top-color:#fff;
            animation:spin .8s linear infinite;
        }
        .paying-overlay p { margin:0; font-size:1.05rem; font-weight:600; }
        .paying-overlay small { color:rgba(255,255,255,.75); font-size:.85rem; }
        @keyframes spin { to { transform:rotate(360deg); } }

        /* Bloques del overlay (spinner / éxito) */
        .overlay-block { display:flex; flex-direction:column; align-items:center; gap:1rem; text-align:center; }

        /* Check de éxito animado (estilo confirmación de compra) */
        .success-circle {
            width:92px; height:92px; border-radius:50%; background:#16a34a;
            display:flex; align-items:center; justify-content:center;
            box-shadow:0 8px 24px rgba(22,163,74,.45);
            animation:pop .4s cubic-bezier(.2,.8,.3,1.4);
        }
        @keyframes pop { 0%{transform:scale(0)} 60%{transform:scale(1.12)} 100%{transform:scale(1)} }
        .success-circle svg { width:50px; height:50px; }
        .success-circle svg path {
            fill:none; stroke:#fff; stroke-width:2.6; stroke-linecap:round; stroke-linejoin:round;
            stroke-dasharray:48; stroke-dashoffset:48; animation:draw .4s .28s ease forwards;
        }
        @keyframes draw { to { stroke-dashoffset:0; } }
        .pay-success-btn {
            margin-top:.5rem; background:#fff; color:#16a34a; font-weight:700;
            padding:.7rem 1.5rem; border-radius:10px; text-decoration:none; font-size:.95rem;
            transition:background .15s ease;
        }
        .pay-success-btn:hover { background:#f0fdf4; }

        /* Cerrar el overlay de éxito (X arriba y enlace abajo) */
        .pay-close-x {
            position:absolute; top:18px; right:20px;
            width:40px; height:40px; padding:0; border:0; border-radius:50%;
            background:rgba(255,255,255,.15); color:#fff;
            font-size:1.7rem; line-height:1; cursor:pointer;
            transition:background .15s ease;
        }
        .pay-close-x:hover { background:rgba(255,255,255,.3); }
        .pay-close-link {
            margin-top:.15rem; background:none; border:0; cursor:pointer;
            color:rgba(255,255,255,.8); font-size:.85rem; text-decoration:underline;
            padding:.25rem .5rem;
        }
        .pay-close-link:hover { color:#fff; }

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
                box-shadow:0 6px 22px rgba(255,122,0,.4);
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <a class="back" href="/"><i data-lucide="arrow-left"></i> Volver al inicio</a>

        <div class="head">
            <h1>Compra publicaciones</h1>
            <p>Elige un plan y publica tus anuncios. Los créditos no vencen.</p>
        </div>

        {{-- Aviso si no hay sesión (lo controla el JS) --}}
        <div id="needLogin" style="display:none; max-width:460px; margin:0 auto; background:#fff; border-radius:16px; padding:1.75rem; text-align:center; box-shadow:0 6px 28px rgba(0,0,0,.08);">
            <h2 style="font-size:1.2rem; margin:0 0 .4rem;">Inicia sesión para comprar</h2>
            <p style="color:var(--muted); margin:0 0 1.25rem;">Necesitas una cuenta para comprar un plan y publicar. Ingresa con Google o Microsoft.</p>
            <div style="display:flex; flex-direction:column; gap:.6rem;">
                <a href="/auth/google/redirect" style="display:inline-flex;align-items:center;justify-content:center;gap:.55rem;background:#fff;color:#1F2937;border:1px solid #D1D5DB;padding:.7rem;border-radius:10px;text-decoration:none;font-weight:500;"><svg viewBox="0 0 48 48" width="18" height="18" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg> Ingresar con Google</a>
                <a href="/auth/microsoft/redirect" style="display:inline-flex;align-items:center;justify-content:center;gap:.55rem;background:#fff;color:#1F2937;border:1px solid #D1D5DB;padding:.7rem;border-radius:10px;text-decoration:none;font-weight:500;"><svg viewBox="0 0 21 21" width="16" height="16" aria-hidden="true"><rect x="1" y="1" width="9" height="9" fill="#F25022"/><rect x="11" y="1" width="9" height="9" fill="#7FBA00"/><rect x="1" y="11" width="9" height="9" fill="#00A4EF"/><rect x="11" y="11" width="9" height="9" fill="#FFB900"/></svg> Ingresar con Microsoft</a>
            </div>
        </div>

        {{-- ── Planes (precios desde el backend) ── --}}
        <div class="plans" id="plansSection">
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
                            <li><i data-lucide="check"></i> {{ $f }}</li>
                        @endforeach
                    </ul>
                    <div class="pick">Seleccionar</div>
                </div>
            @endforeach
        </div>

        {{-- ── Checkout ── --}}
        <div class="checkout" id="checkoutSection">
            <h2>Tus datos</h2>

            {{-- Resumen compacto cuando hay sesión (lo llena el JS) --}}
            <div id="identitySummary" class="identity-summary" style="display:none;">
                <div>
                    <div style="font-size:.72rem; color:var(--muted); font-weight:600;">PAGAS COMO</div>
                    <div id="sumName" style="font-weight:600;"></div>
                    <div id="sumEmail" style="color:var(--muted); font-size:.85rem;"></div>
                </div>
                <a href="#" id="editIdentity">Editar</a>
            </div>

            {{-- Campos completos (se ocultan si hay sesión; se pueden reabrir con "Editar") --}}
            <div id="identityFields">
                <div class="row">
                    <div>
                        <label for="firstName">Nombres</label>
                        <input type="text" id="firstName" placeholder="Tus nombres" autocomplete="given-name">
                    </div>
                    <div>
                        <label for="lastName">Apellidos</label>
                        <input type="text" id="lastName" placeholder="Tus apellidos" autocomplete="family-name">
                    </div>
                </div>
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" placeholder="tucorreo@ejemplo.com" autocomplete="email">
            </div>

            <label for="phone">Celular <span style="color:#DC2626">*</span></label>
            <input type="tel" id="phone" placeholder="9XXXXXXXX" maxlength="9" inputmode="numeric" required>
            <small style="color:var(--muted); font-size:.78rem;">9 dígitos, empieza con 9. Obligatorio para el pago.</small>

            <button id="btnPay" type="button" disabled>Selecciona un plan</button>

            <div class="result" id="result"></div>
            <p class="secure" style="display:flex;align-items:center;justify-content:center;gap:.35rem;"><i data-lucide="lock" style="width:13px;height:13px;"></i> Pago seguro con Culqi. Tus datos de tarjeta nunca pasan por este sitio.</p>
        </div>
    </div>

    {{-- Overlay de pago en proceso: bloquea la pantalla para no pagar dos veces --}}
    <div class="paying-overlay" id="payingOverlay" role="alert" aria-live="assertive" aria-hidden="true">
        {{-- La X solo se muestra cuando el pago ya está confirmado (no durante "Procesando...") --}}
        <button type="button" id="payCloseX" class="pay-close-x" aria-label="Cerrar" style="display:none;">&times;</button>
        <div id="payingSpinner" class="overlay-block">
            <div class="spinner"></div>
            <p id="payingText">Procesando tu pago...</p>
            <small>No cierres ni recargues esta página.</small>
        </div>
        <div id="paySuccess" class="overlay-block" style="display:none;">
            <div class="success-circle">
                <svg viewBox="0 0 24 24"><path d="M4 12.5l5 5L20 6"/></svg>
            </div>
            <p style="font-size:1.3rem;">¡Pago realizado!</p>
            <small id="paySuccessMsg">Tus créditos se agregaron a tu cuenta.</small>
            <a href="/publicar" id="paySuccessBtn" class="pay-success-btn">Publicar anuncio</a>
            <button type="button" id="paySuccessClose" class="pay-close-link">Cerrar</button>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    {{-- SOLO CDN oficial de Culqi --}}
    <script src="https://checkout.culqi.com/js/v4"></script>
    <script>
        if (window.lucide) lucide.createIcons();
        const TITLE        = @json(config('app.name'));
        const CSRF         = document.querySelector('meta[name="csrf-token"]').content;
        const URL_CARGO    = @json(route('pago.cargo'));
        const URL_ORDEN    = @json(route('pago.orden'));
        const URL_CONFIRMAR = @json(route('pago.orden.confirmar'));

        let selectedPlan   = null;
        let selectedAmount = 0;   // céntimos (solo para mostrar en el widget)
        let procesando     = false; // guard contra doble pago / doble disparo del callback

        Culqi.publicKey = @json(config('culqi.public_key'));

        // Intenta precargar datos si hay sesión, pero permite compra sin autenticación.
        (async () => {
            let me;
            try {
                me = await (await fetch('/me', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })).json();
            } catch (e) { return; }

            if (me.authenticated) {
                const parts = (me.user.name || '').trim().split(/\s+/);
                if (parts[0]) document.getElementById('firstName').value = parts.shift();
                if (parts.length) document.getElementById('lastName').value = parts.join(' ');
                if (me.user.email) document.getElementById('email').value = me.user.email;
                document.getElementById('sumName').textContent  = me.user.name || '';
                document.getElementById('sumEmail').textContent = me.user.email || '';
                document.getElementById('identityFields').style.display = 'none';
                document.getElementById('identitySummary').style.display = 'flex';
            }
        })();

        // "Editar" → reabre los campos completos.
        document.getElementById('editIdentity').addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('identityFields').style.display = '';
            document.getElementById('identitySummary').style.display = 'none';
        });

        // Celular: solo dígitos, máximo 9.
        document.getElementById('phone').addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 9);
        });

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
                    bannerColor: '#FF7A00',
                    buttonBackground: '#FF7A00',
                    menuColor: '#FF7A00',
                    linksColor: '#FF7A00',
                    buttonText: 'Pagar',   // Culqi le agrega el monto solo (ej: "Pagar S/ 1.00")
                    buttonTextColor: '#ffffff',
                    priceColor: '#1F2937',
                },
            });
        }

        document.getElementById('btnPay').addEventListener('click', abrirCheckout);

        // Cierre del overlay de éxito: X, enlace "Cerrar", clic en el fondo y tecla Esc.
        document.getElementById('payCloseX').addEventListener('click', closePaidOverlay);
        document.getElementById('paySuccessClose').addEventListener('click', closePaidOverlay);
        document.getElementById('payingOverlay').addEventListener('click', (e) => {
            // Solo si ya está en estado de éxito (la X visible); nunca durante "Procesando...".
            if (e.target === e.currentTarget && document.getElementById('payCloseX').style.display !== 'none') {
                closePaidOverlay();
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && document.getElementById('payCloseX').style.display !== 'none') {
                closePaidOverlay();
            }
        });

        async function abrirCheckout() {
            if (!selectedPlan) return;

            // La orden de Culqi exige nombres, apellidos, correo y CELULAR.
            // Validamos aquí para no fallar en silencio (y perder métodos de pago).
            const email = val('email'), firstName = val('firstName'),
                  lastName = val('lastName'), phone = val('phone');

            if (!firstName || !lastName || !email) {
                // Reabre los campos completos si faltan datos de identidad.
                document.getElementById('identityFields').style.display = '';
                document.getElementById('identitySummary').style.display = 'none';
                mostrar('err', 'Completa tus nombres, apellidos y correo antes de pagar.');
                return;
            }
            if (!/^9\d{8}$/.test(phone)) {
                mostrar('err', 'Ingresa tu número de celular (9 dígitos, empieza con 9) para continuar.');
                const p = document.getElementById('phone');
                p.focus(); p.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            procesando = false;   // nuevo intento de pago
            const btn = document.getElementById('btnPay');
            btn.disabled = true; btn.textContent = 'Preparando...';

            // Creamos una orden (habilita PagoEfectivo/Cuotéalo). Enviamos el PLAN, no el monto.
            try {
                const res = await fetch(URL_ORDEN, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    credentials: 'same-origin',
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
                setPaying(true);                                // bloquea la pantalla
                confirmarOrden(Culqi.order.id);                 // la orden es el pago
            } else if (Culqi.token) {
                setPaying(true);                                // bloquea la pantalla
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
            try {
                const res = await fetch(URL_CONFIRMAR, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ order_id: orderId }),
                });
                const data = await res.json();
                if (data.success && data.paid) {
                    mostrar('ok', 'Pago confirmado. ¡Gracias! (orden ' + orderId + ')');
                    showPaidOverlay();
                } else if (data.success) {
                    mostrar('ok', 'Orden ' + orderId + ' generada. Completa el pago con las instrucciones; lo confirmaremos automáticamente.');
                    finishPayment(true);
                } else {
                    mostrar('err', data.message || 'No se pudo verificar el pago.');
                    finishPayment(false);
                }
            } catch (e) {
                mostrar('err', 'Error de conexión. Intenta nuevamente.');
                finishPayment(false);
            }
        }

        // Fallback: cargo por token cuando NO se pudo crear la orden
        async function enviarCargo(tokenId, email) {
            try {
                const res = await fetch(URL_CARGO, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        token: tokenId,
                        plan: selectedPlan,          // el precio lo pone el backend
                        email: email || val('email'),
                        first_name: val('firstName'),
                        last_name: val('lastName'),
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    mostrar('ok', data.message + ' (cargo ' + data.charge_id + ')');
                    showPaidOverlay();
                } else {
                    mostrar('err', data.message || 'No se pudo procesar el pago.');
                    finishPayment(false);
                }
            } catch (e) {
                mostrar('err', 'Error de conexión. Intenta nuevamente.');
                finishPayment(false);
            }
        }

        function val(id) { return document.getElementById(id).value.trim(); }

        function closeCulqi() {
            try { if (window.Culqi && typeof Culqi.close === 'function') Culqi.close(); } catch (e) {}
        }

        // Muestra/oculta el overlay; al mostrarlo vuelve al estado "spinner".
        function setPaying(show, text) {
            const ov = document.getElementById('payingOverlay');
            if (show) {
                document.getElementById('payingSpinner').style.display = 'flex';
                document.getElementById('paySuccess').style.display = 'none';
            }
            if (text) document.getElementById('payingText').textContent = text;
            ov.classList.toggle('show', show);
            ov.setAttribute('aria-hidden', show ? 'false' : 'true');
        }

        // Pago CONFIRMADO → check verde animado + botón para continuar (no se paga 2 veces).
        async function showPaidOverlay() {
            closeCulqi();
            const btn = document.getElementById('btnPay');
            btn.disabled = true; btn.textContent = 'Pago realizado';

            // Muestra el saldo de publicaciones ya actualizado.
            let saldo = null;
            try {
                const me = await (await fetch('/me', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })).json();
                if (me.authenticated) saldo = me.user.credits;
            } catch (e) {}

            document.getElementById('paySuccessMsg').textContent = saldo != null
                ? `Ya tienes ${saldo} publicación${saldo === 1 ? '' : 'es'} disponible${saldo === 1 ? '' : 's'}.`
                : 'Tus créditos se agregaron a tu cuenta.';
            const go = document.getElementById('paySuccessBtn');
            go.href = '/publicar';
            go.textContent = 'Publicar anuncio';

            document.getElementById('payingSpinner').style.display = 'none';
            document.getElementById('paySuccess').style.display = 'flex';
            document.getElementById('payCloseX').style.display = 'block';
            const ov = document.getElementById('payingOverlay');
            ov.classList.add('show'); ov.setAttribute('aria-hidden', 'false');
        }

        // Cierra el overlay tras un pago confirmado (la X solo existe en ese estado).
        function closePaidOverlay() {
            const ov = document.getElementById('payingOverlay');
            ov.classList.remove('show');
            ov.setAttribute('aria-hidden', 'true');
            document.getElementById('payCloseX').style.display = 'none';
        }

        // ok=true: orden generada (pago pendiente). ok=false: error → permite reintentar.
        function finishPayment(ok) {
            closeCulqi();
            setPaying(false);
            const btn = document.getElementById('btnPay');
            if (ok) {
                btn.disabled = true; btn.textContent = 'Orden generada';
                document.getElementById('result').scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                procesando = false; btn.disabled = false;
                btn.textContent = selectedAmount ? 'Pagar S/ ' + (selectedAmount / 100).toFixed(2) : 'Selecciona un plan';
            }
        }
        function mostrar(tipo, msg) {
            const box = document.getElementById('result');
            box.className = 'result show ' + tipo;
            box.textContent = msg;
        }
    </script>
</body>
</html>
