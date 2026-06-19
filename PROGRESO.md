# 📋 Progreso — anuncialo.pe (Clasificados + Pasarela Culqi)

> Última actualización: **2026-06-19**.
> Estado: **en producción (sandbox Culqi)** en `https://anuncialo.pe`.
> Modelo de negocio actual: **créditos de publicación** — el usuario nuevo recibe **20 créditos gratis** al registrarse; los planes de pago están **ocultos por ahora** (el checkout sigue funcionando por dentro).

---

## 🗓️ Bitácora — del inicio al estado actual

### 2026-05-29 — Arranque del proyecto
- Estructura inicial del proyecto (Laravel) + Docker.
- Primeras rutas de la API de pruebas:
  `GET /api/health`, `POST /api/payment/charge`, `POST /api/payment/yape`, `GET /api/payment/{id}`.

### 2026-05-30 — Integración Culqi base + conexión a cPanel
- Integración de pagos **Yape** y tarjeta sobre el SDK `culqi/culqi-php`.
- Comando que **verifica en terminal** la conexión a BD y a Culqi al levantar el server.
- `.env.example` ordenado (Culqi + RSA + MySQL).
- Primer README de la pasarela + guía de progreso (webhook + ngrok).
- Conexión del proyecto con cPanel.

### 2026-06-01 — Clasificados reales + login + moderación
- **Login social OAuth** (Google / Microsoft) con Socialite.
- **Anuncios en BD**: el home muestra anuncios reales y al usuario logueado (foto/nombre).
- **Publicar anuncios reales**, URLs limpias, menú de perfil, login requerido para publicar.
- **Webhook idempotente** (tabla `webhook_events`).
- **Moderación de contenido** (groserías), fechas reales, anti-flash de sesión.

### 2026-06-03 — Despliegue a producción + UI clasificados
- **Desplegado en producción** `https://anuncialo.pe` (cPanel) vía Git + SSH.
- **Login Google** funcionando en producción.
- **Yape arreglado** (se aceptaban solo tokens `tkn_`; ahora también `ype_`).
- **UI clasificados**: filtros de ubicación (Depto → Prov → Distrito) en navbar responsive; categorías Empleo/Busco.
- Plan básico a **S/ 1.00** para pruebas; fix de precio duplicado en el widget.
- Se captura el **nombre del comprador** (`transactions.customer_name`).
- **Webhooks registrados** en CulqiPanel.
- Guía de despliegue [`DEPLOY.md`](DEPLOY.md).

### 2026-06-11 — Automatización del deploy
- Script [`deploy.sh`](deploy.sh): setup automático en cPanel después del clone
  (composer, migrate, optimize, permisos).

### 2026-06-17 — Responsive, caché y robustez de la UI
- **Grilla de anuncios fluida** (responsive de verdad) + descripción limitada a **144** en BD.
- **Cache-busting automático** de assets (el usuario siempre ve lo último, sin incógnito).
- No-cache en HTML + revalidación de CSS/JS en `.htaccess` + LiteSpeed desactivado.
- Pulido **responsive móvil** (pills deslizables, tarjetas táctiles).
- `Ad::MAX_DESCRIPTION` centralizado (DRY).
- Fix: los anuncios **Nacional** ahora se muestran y filtran bien.
- Fix: texto de filtros de ubicación y de "Mis anuncios" ya no se corta/monta.
- **Modal de advertencia** al publicar contenido no permitido.

### 2026-06-18 — Arquitectura, seguridad, pivote a créditos y rediseño
- **Revisión y refactor de arquitectura** (pagos/anuncios): Actions, FormRequests, Events/Listeners, acciones reales (activar/eliminar con propiedad).
- **Checkout anti doble-pago**: overlay "procesando", cierra el modal de Culqi al confirmar, "Tus datos" compacto, **confirmación animada** (check verde estilo compra).
- ⭐ **Pivote del modelo de negocio**: los planes pasan de "destacar" a **créditos de publicación** (publicar gasta 1 crédito). Se eliminó el featuring (`FeatureAdOnPayment`) y se creó `GrantCreditsOnPayment`.
- **Seguridad**: CORS restringido al dominio (`config/cors.php`), **rate limiting** por usuario/min, **XSS** (escape `e()` del texto de anuncios en la API).
- **README** completo del proyecto.
- **Aviso por correo al admin** cuando se confirma un pago (`NotifyAdminOnPayment` + `PaymentReceivedMail`).
- Fix global de dominio `enlix.pe` → `anuncialo.pe`.
- **Rediseño premium del frontend**: design system con paleta única (azul/ámbar/verde), tipografía **Inter**, iconos **Lucide** (sin Font Awesome ni emojis), logos oficiales de Google/Microsoft. Propagado a home, publicar, mis-anuncios, completar-perfil y checkout. Mis anuncios en **cuadrícula compacta**.
- **20 créditos gratis** al registrarse + **planes ocultos** por ahora.

### 2026-06-19 — Migración de hosting, rendimiento y Papelera
- **Migración a un cPanel nuevo** (cuenta `anuncial`): base `anuncial_culqi_bd` creada, `.env` actualizado, tablas migradas. SSH/Terminal documentado (usuario `anuncial`).
- **Rendimiento (escala a miles de anuncios):**
  - `/api/ads` ahora **filtra y pagina en el servidor** (24 por página) en vez de mandar cientos al navegador. Búsqueda con *debounce* y paginación con ventana.
  - `/mis-anuncios/datos` también **paginado** + conteos calculados en BD.
  - **Índices compuestos** en `ads` (`status,created_at` · `status,category,created_at` · `status,department` · `coverage`).
- **Papelera de anuncios:** eliminar = **soft delete** (`deleted_at`, queda en BD para el futuro panel admin). Pestaña **Papelera** con restaurar dentro de **30 días**; luego el comando `ads:purge-trash` (programado a diario) los borra de verdad.
- **Herramientas de admin (terminal):** `ads:trash` (listar/restaurar/purgar) y `ads:purge-trash`.
- **Seeder de demo** `AdsSeeder` (`php artisan db:seed --class=AdsSeeder`) para poblar anuncios de prueba.

---

## ✅ Estado actual — hecho y funcionando

### Infraestructura / Despliegue
- Producción en `https://anuncialo.pe` (cPanel, hosting enlix).
- Flujo: PC → GitHub (`kitikazis/pasarela-culqi`) → servidor (`~/public_html`).
- Deploy con **`git pull && bash deploy.sh`**.
- `.htaccess` raíz redirige a `public/` (ver [`DEPLOY.md`](DEPLOY.md)).

### Backend (Laravel 12 + SDK Culqi)
- `CulqiService` (charge, yape, refund, customer, card, order, token, ping).
- `PaymentController` (cargo, orden, confirmar, refund, guardar-tarjeta, webhook).
- Actions: `RecordTransaction` (persistencia atómica), `ProcessCulqiWebhook` (idempotencia + anti-spoofing).
- Events/Listeners: `PaymentConfirmed` → `GrantCreditsOnPayment` + `NotifyAdminOnPayment`.
- `config/plans.php` (precios autoritativos en backend).

### Negocio (créditos)
- **20 créditos gratis** al crear la cuenta (solo en alta, no resetea a existentes).
- Publicar gasta **1 crédito** de forma atómica (transacción de BD).
- Planes (checkout) **ocultos en la UI** por ahora; el flujo de compra sigue intacto por dentro.

### Métodos de pago (sandbox)
| Método | Estado |
| --- | --- |
| Tarjeta | ✅ Cobra (sandbox) |
| Yape | ✅ Funciona (cel. `999999999`, OTP `123456`) |
| Orden (PagoEfectivo/Cuotéalo) | ✅ Se genera |
| Devolución | ✅ Código listo (solo admin) |
| Guardar tarjeta | ✅ Código listo |

### Frontend
- Rediseño premium aplicado a las 5 páginas (Inter + Lucide + paleta única).
- Home con anuncios reales, filtros y búsqueda **paginados en el servidor**.
- Mis anuncios paginado + pestaña **Papelera** (restaurar / días restantes).
- Publicar, Completar perfil; Checkout Blade.

---

## 🔜 Pendientes (orden sugerido)

### 🔴 1. Correo en producción (SMTP)
`MAIL_MAILER=log` → **no llegan** los avisos de pago. Configurar SMTP de cPanel
(`mail.anuncialo.pe`, buzón `noreply@anuncialo.pe`). Ver checklist en [`DEPLOY.md`](DEPLOY.md).

### 🔴 2. Rotar secretos expuestos
Password de BD y `GOOGLE_CLIENT_SECRET` (se compartieron varias veces). Rotar por precaución.

### ✅ 3. Paginación real de `/api/ads` — HECHO (2026-06-19)
Server-side en home y mis-anuncios + índices compuestos. (Pendiente menor: índice FULLTEXT para la búsqueda, hoy usa `LIKE`.)

### 🔴 4. Configurar el CRON del scheduler en cPanel
La purga de la Papelera (`ads:purge-trash`) necesita un cron que dispare el scheduler:
`* * * * * cd ~/public_html && php artisan schedule:run >> /dev/null 2>&1`. Ver [`DEPLOY.md`](DEPLOY.md).

### 🟡 5. Probar el webhook end-to-end
Comprobar que una orden PagoEfectivo pase de `pending` → `paid` sola cuando Culqi avisa.

### 🟢 5. Go-live real (cuando se quiera cobrar)
- Llaves **live** (`pk_live`/`sk_live`) + **RSA** (`rs_live`) + aprobación comercial de Culqi.
- **Reactivar los botones de planes** (hoy comentados en home y mis-anuncios).
- Precios reales de planes (el básico está en S/ 1.00 de prueba).

### 🔵 6. Calidad / limpieza
- **Tests** de los flujos de pago.
- Quitar/definir el `<img src="">` placeholder en `mis-anuncios.html`.
- Actualizar [`API.md`](API.md) (quedó de la etapa de la API de pruebas).

---

## 📝 Datos de referencia (sandbox)

- **Tarjeta:** `4111 1111 1111 1111`, CVV `123`, exp `09/2028`.
- **Yape:** celular `999999999`, OTP `123456`.
- **Mínimo Culqi para Yape/billeteras:** S/ 6.00 (con S/ 1.00 solo sale tarjeta).
- **RSA desactivado** en sandbox; se activa solo con llaves reales.
- **Sandbox = sin dinero real** (todo simulado).

---

## 📂 Tablas de BD
- **`users`** ✅ — login social + `publish_credits`.
- **`ads`** ✅ — anuncios (categoría, cobertura, ubicación).
- **`transactions`** ✅ — cada pago/intento (+ `customer_name`).
- **`webhook_events`** ✅ — idempotencia del webhook.
- **`saved_cards`** ⏳ — solo si se harán cobros one-click.

---

## 🚀 Cómo retomar

```bash
# Local (editar)
cd c:\Users\luisk\cliente\pasarela-culqi
php artisan serve            # http://localhost:8000

# Subir cambios:   git add . && git commit -m "..." && git push origin main
# Desplegar (SSH): cd ~/public_html && git pull && bash deploy.sh
```
