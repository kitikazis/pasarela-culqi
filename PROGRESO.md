# 📋 Progreso — Pasarela Culqi / Clasificados

> Última actualización: 2026-06-03. Estado: **en producción (sandbox)** en `https://enlix.pe`.

---

## 🗓️ Qué hicimos en la sesión 2026-06-03

- ✅ **Desplegado en producción** `https://enlix.pe` (cPanel) con flujo Git + SSH
- ✅ **Login Google** funcionando en producción (redirect URIs configurados)
- ✅ **Yape arreglado** — se aceptaban solo tokens `tkn_`; ahora también `ype_`
- ✅ **UI clasificados** — filtros ubicación (Depto/Prov/Distrito) en navbar responsive, sin logo, grilla full-width
- ✅ **Categorías** renombradas: Trabajo→Empleo, Se busca→Busco (solo etiqueta, BD intacta)
- ✅ **Planes responsive** + botón de pago sticky en móvil
- ✅ **Nombre del comprador** — se captura y guarda en `transactions.customer_name` (migración aplicada)
- ✅ **Webhooks registrados** en CulqiPanel (charge→creation→succeeded · order→status→succeeded)

---

## ✅ Lo que YA está hecho y funcionando

### Infraestructura / Despliegue
- Producción en `https://enlix.pe` (cPanel, hosting enlix)
- Flujo Git: PC → GitHub (`kitikazis/pasarela-culqi`) → servidor (`~/public_html`)
- Deploy = `git pull && php artisan migrate --force && php artisan optimize:clear`
- `.htaccess` en `public_html` redirige a `public/` (guía en [`DEPLOY.md`](DEPLOY.md))

### Backend (Laravel 12 + SDK Culqi)
- **CulqiService** — charge, yape, refund, customer, card, order, token, ping
- **PaymentController** — checkout, charge, yape, refund, saveCard, createOrder, confirmOrder, webhook, health
- **Transaction** — email encriptado, `customer_name`, soft deletes
- **config/plans.php** — precios autoritativos en backend
- **Webhook** — código listo + tabla `webhook_events` (idempotencia) + registrado en Culqi

### Métodos de pago
| Método | Estado |
| --- | --- |
| 💳 Tarjeta | ✅ Cobra en producción (sandbox) |
| 📱 Yape | ✅ Funciona (cel. `900000001`, OTP 6 díg. cualquiera) |
| 🔁 Devolución | ✅ Código listo |
| 📦 Orden (PagoEfectivo/Cuotéalo) | ✅ Se genera |
| 💾 Guardar tarjeta | ✅ Código listo |

### Frontend / UI
- Home con anuncios reales (`/api/ads`), filtros de ubicación, categorías Empleo/Busco
- Publicar anuncio, Mis anuncios, Completar perfil
- Página de planes responsive

---

## 🔜 PENDIENTES — retomar mañana (en orden)

> 👉 **EMPEZAR POR EL #1** (es lo que le da sentido al negocio).

### 🟡 1. ⭐ Ligar pago ↔ anuncio  ← SIGUIENTE
Hoy se cobra pero **no se destaca ningún anuncio**. `FeatureAdOnPayment` destaca el anuncio
solo si la transacción tiene `ad_id`, pero el checkout **nunca envía qué anuncio** se destaca.
Falta: pasar el `ad_id` desde "Mis anuncios" → checkout → guardarlo en la transacción.

### 🟡 2. Probar el webhook end-to-end
Los webhooks ya están registrados. Falta **comprobar** que una orden PagoEfectivo pase de
`pending` → `paid` sola cuando Culqi avisa. (Hoy la fila 45 quedó `pending` esperando esto.)

### 🔴 3. Seguridad
- **Proteger endpoints abiertos** — `/pago/devolucion` y `/pago/guardar-tarjeta` están públicos.
  Añadir `auth` + rol admin (sobre todo la devolución).
- **Rotar secretos** — password de BD y `GOOGLE_CLIENT_SECRET` (quedaron expuestos cuando el
  proyecto estuvo en el webroot con `APP_DEBUG=true`).

### 🟢 4. Producción real (go-live)
- Llaves **live** (`pk_live`/`sk_live`) + **RSA** (`rs_live`) + **aprobación comercial** de Culqi
- **Precios reales** de planes (hoy el básico está en S/1.00 de prueba)
- **Correos** — `MAIL_MAILER=log` no envía nada; configurar SMTP para recibos
- Página de **confirmación / recibo** tras el pago

### 🔵 5. Calidad
- **Tests** de los flujos de pago
- **Centralizar categorías** en `config/categories.php`

---

## 📝 Datos de referencia (sandbox)

- **Tarjeta:** `4111 1111 1111 1111`, CVV `123`, exp `09/2028`
- **Yape:** celular `900000001`, OTP cualquier 6 dígitos (ej. `123456`)
- **Mínimo Culqi para Yape/billeteras:** S/ 6.00 (en S/1.00 solo sale tarjeta)
- **RSA desactivado** en sandbox; se activa solo con llaves reales
- **Sandbox = sin dinero real** (todo simulado)

---

## 📂 Tablas de BD
- **`users`** ✅ — login social
- **`ads`** ✅ — anuncios (categoría, cobertura, ubicación, `featured_until`)
- **`transactions`** ✅ — cada pago/intento (+ `customer_name`)
- **`webhook_events`** ✅ — idempotencia del webhook
- **`saved_cards`** ⏳ — solo si se harán cobros one-click

---

## 🚀 Cómo retomar mañana

```bash
# Local (editar)
cd c:\Users\luisk\pasarela-culqi
php artisan serve            # http://localhost:8000

# Subir cambios:  git add . && git commit -m "..." && git push
# Desplegar (SSH):  cd ~/public_html && git pull && php artisan optimize:clear
#   (agrega 'php artisan migrate --force' si hay migración nueva)
```
