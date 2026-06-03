# 📋 Progreso — Pasarela Culqi / Clasificados

> Última actualización: 2026-06-03. Estado: **en producción (sandbox)** en `https://enlix.pe`.

---

## ✅ Lo que YA está hecho y funcionando

### Infraestructura / Despliegue
- **Desplegado en producción** en `https://enlix.pe` (cPanel, hosting enlix)
- **Flujo Git** montado: PC → GitHub (`kitikazis/pasarela-culqi`) → servidor (`~/public_html`)
- Deploy = `git pull && php artisan migrate --force && php artisan optimize:clear`
- `.htaccess` en `public_html` redirige a `public/` (el dominio principal no deja cambiar el Document Root)
- Guía completa en [`DEPLOY.md`](DEPLOY.md)

### Backend (Laravel 12 + SDK Culqi)
- **CulqiService** — charge, yape, refund, customer, card, order, token, ping
- **PaymentController** — showCheckout, charge, yape, refund, saveCard, createOrder, confirmOrder, webhook, health
- **Transaction** (modelo) — email encriptado, `customer_name`, soft deletes
- **Form Requests** — Charge, Yape, Refund, CreateOrder
- **config/plans.php** — precios autoritativos en backend
- **webhook_events** — tabla para idempotencia/auditoría del webhook

### Métodos de pago
| Método | Estado |
| --- | --- |
| 💳 Tarjeta | ✅ Cobra en producción (sandbox) |
| 📱 Yape | ✅ **Funciona** (cel. prueba `900000001`, OTP cualquiera 6 díg.) |
| 🔁 Devolución | ✅ Código listo |
| 📦 Orden (PagoEfectivo/Cuotéalo) | ✅ Se genera |
| 💾 Guardar tarjeta | ✅ Código listo |

### Frontend / UI (clasificados)
- Login social **Google** funcionando en producción (Microsoft pendiente de credenciales)
- Home con anuncios reales desde la BD (`/api/ads`)
- **Filtros de ubicación** Departamento → Provincia → Distrito (en navbar, responsive)
- Categorías: Venta · Compra · **Empleo** · **Busco** (claves internas siguen `trabajo`/`busca`)
- Página de planes responsive + botón de pago sticky en móvil
- Publicar anuncio, Mis anuncios, Completar perfil
- Nombre del comprador se captura y guarda (`transactions.customer_name`)

---

## 🔜 PENDIENTES (orden sugerido)

### 🔴 Seguridad
1. **Proteger endpoints abiertos** — `/pago/devolucion` y `/pago/guardar-tarjeta` están públicos. Añadir `auth` + rol admin (sobre todo la devolución).
2. **Rotar secretos expuestos** — password de BD y `GOOGLE_CLIENT_SECRET` (quedaron a la vista cuando el proyecto estuvo en el webroot con `APP_DEBUG=true`).

### 🟡 Funcionalidad core
3. **⭐ Ligar pago ↔ anuncio** — `FeatureAdOnPayment` destaca el anuncio solo si la transacción tiene `ad_id`, pero el checkout NO envía qué anuncio se destaca. Hoy se cobra pero **no se destaca ningún anuncio**. Falta pasar el `ad_id`: Mis anuncios → checkout → transacción.
4. **Registrar webhook en producción** — en CulqiPanel → Desarrollo → Webhooks: `https://enlix.pe/culqi/webhook`.
5. **Página de confirmación / recibo** tras el pago.

### 🟢 Producción (go-live real)
6. Llaves **live** (`pk_live`/`sk_live`) + **RSA** (`rs_live`) + **aprobación comercial** de Culqi.
7. **Precios reales** de planes (hoy el básico está en **S/1.00** de prueba).
8. **Correos** — `MAIL_MAILER=log` no envía nada; configurar SMTP para recibos/confirmaciones.

### 🔵 Calidad
9. **Tests** de los flujos de pago (hay phpunit, faltan pruebas).
10. **Centralizar categorías** en `config/categories.php` (hoy las etiquetas viven en JS + form + validación).

---

## 📝 Datos de referencia (sandbox)

- **Tarjeta de prueba:** `4111 1111 1111 1111`, CVV `123`, exp `09/2028`.
- **Yape de prueba:** celular `900000001`, OTP cualquier 6 dígitos (ej. `123456`).
- **Mínimo Culqi para Yape/billeteras:** S/ 6.00 (en S/1.00 solo sale tarjeta).
- **RSA desactivado** en sandbox (`CULQI_RSA_ID` vacío). Se activa solo con llaves reales.
- **Sandbox = sin dinero real.** Todo es simulado.

---

## 📂 Tablas de BD
- **`users`** ✅ — login social (Google/Microsoft)
- **`ads`** ✅ — anuncios (categoría, cobertura, ubicación, `featured_until`)
- **`transactions`** ✅ — cada pago/intento (+ `customer_name`)
- **`webhook_events`** ✅ — idempotencia del webhook
- **`saved_cards`** ⏳ — solo si se harán cobros one-click/recurrentes
