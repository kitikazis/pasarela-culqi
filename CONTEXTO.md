# 🧠 Contexto del proyecto — anuncialo.pe (para una IA)

> Documento **autocontenido** para que un asistente de IA entienda TODO el proyecto
> sin leer el resto del código. Última actualización: 2026-06-19.

---

## 1. Qué es

**anuncialo.pe** es una plataforma web de **anuncios clasificados** para Perú, con una
**pasarela de pagos Culqi** integrada. Los usuarios inician sesión (Google/Microsoft),
**publican anuncios** gastando "créditos de publicación", y pueden **comprar más créditos**
pagando con Culqi.

- **Producción:** `https://anuncialo.pe` (cPanel, hosting compartido).
- **Estado:** funcional, en **sandbox de Culqi** (llaves de prueba; no cobra dinero real).
- **Repo:** `github.com/kitikazis/pasarela-culqi` (rama `main`).

---

## 2. Modelo de negocio (IMPORTANTE)

- Publicar un anuncio **gasta 1 crédito** (`users.publish_credits`).
- Al **registrarse**, el usuario recibe **9999 créditos gratis** (constante
  `WELCOME_CREDITS` en `AuthController`). → En la práctica nadie se queda sin publicar.
- **Comprar un plan** suma más créditos (no vencen). Los planes se definen en
  `config/plans.php` (precio en céntimos + créditos otorgados).
- **Los planes están OCULTOS en la UI por ahora** (botones "Comprar publicaciones" y el
  contador de créditos comentados en el HTML). El **checkout y el flujo de pago siguen
  funcionando por dentro**; reactivarlos es descomentar.
- Históricamente el modelo era "destacar anuncios" (featuring); **se eliminó** y se pivoteó
  a créditos. Queda la columna `ads.featured_until` sin uso activo.

---

## 3. Stack tecnológico

| Capa                  | Tecnología                                                                                                               |
| --------------------- | ------------------------------------------------------------------------------------------------------------------------ |
| Backend               | **Laravel 12**, PHP 8.2+                                                                                                 |
| Base de datos         | MySQL (`anuncial_culqi_bd` en producción)                                                                                |
| Pagos                 | **Culqi** API v2.0 + Custom Checkout v4 (multipago) vía SDK `culqi/culqi-php`                                            |
| Auth                  | Laravel **Socialite** (Google + Microsoft, sin contraseñas)                                                              |
| Frontend público      | **HTML/CSS/JS estático** en `public/`, servido por Laravel (PageController) con cache-busting                            |
| Frontend checkout     | Vista **Blade** (`resources/views/payment/checkout.blade.php`)                                                           |
| Diseño                | Design system propio: tipografía **Inter**, iconos **Lucide** (CDN, sin Font Awesome ni emojis), paleta azul/ámbar/verde |
| Moderación (opcional) | Listas locales + Google Perspective API                                                                                  |

> El frontend NO es React/Vue. Son páginas HTML con JS vanilla que llaman a la API por `fetch`.

---

## 4. Estructura / arquitectura

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AdController.php       # anuncios: listar (paginado), publicar, activar/eliminar, papelera, restaurar
│   │   ├── AuthController.php     # login social (OAuth), /me, logout, +9999 créditos al crear cuenta
│   │   ├── PageController.php     # sirve las páginas HTML con cache-busting (?v=filemtime)
│   │   ├── PaymentController.php  # checkout, cargo, orden, confirmar, refund, guardar-tarjeta, webhook
│   │   └── HealthController.php   # estado de BD + Culqi (oculta detalles salvo APP_DEBUG)
│   ├── Requests/                  # FormRequests (validación por endpoint, whitelist de valores)
│   │   ├── StoreAdRequest.php     # categoría/cobertura/teléfono/descripción(≤144) + regla NoProfanity
│   │   ├── ChargeRequest · CreateOrderRequest · YapeRequest · RefundRequest
│   │   └── SaveCardRequest · ConfirmOrderRequest
│   └── Middleware/
│       ├── EnsureAdmin.php        # alias 'admin': solo correos de ADMIN_EMAILS (devoluciones)
│       └── SecurityHeaders.php    # X-Frame-Options, nosniff, Referrer-Policy, HSTS, etc.
├── Actions/
│   ├── ProcessCulqiWebhook.php    # idempotencia + anti-spoofing (re-consulta a Culqi) + auditoría
│   └── RecordTransaction.php      # persistencia atómica de transacciones
├── Services/
│   ├── CulqiService.php           # capa sobre el SDK culqi/culqi-php (charge, yape, order, refund, RSA)
│   └── ContentModerator.php       # moderación (listas locales + Perspective opcional)
├── Events/PaymentConfirmed.php    # se dispara al confirmarse un pago
├── Listeners/
│   ├── GrantCreditsOnPayment.php  # suma créditos del plan al usuario
│   └── NotifyAdminOnPayment.php   # correo de aviso a ADMIN_EMAILS
├── Mail/PaymentReceivedMail.php   # Mailable del aviso de pago
├── Console/Commands/
│   ├── PurgeTrashedAds.php        # ads:purge-trash → borra definitivamente los de +30 días (cron diario)
│   └── TrashAds.php               # ads:trash → listar / restaurar / purgar (admin por terminal)
├── Rules/NoProfanity.php          # regla de validación de contenido
├── Models/Ad.php · User.php · Transaction.php · WebhookEvent.php
└── Providers/AppServiceProvider.php  # registra Listeners + rate limiters

config/   plans.php · culqi.php · moderation.php · cors.php · app.php
routes/   web.php (navegador, CSRF) · api.php (API) · console.php (scheduler)
resources/views/  payment/checkout.blade.php · emails/payment-received.blade.php
public/   index.html · publicar.html · mis-anuncios.html · completar-perfil.html · styles.css · peru-data.js
database/migrations/  · database/seeders/AdsSeeder.php
```

**Patrones:** Config-driven (planes/moderación/CORS/admins en `config` o `.env`), FormRequests
para validación, Services/Actions para lógica reutilizable, Events/Listeners como "costura"
de negocio (pago → créditos + correo).

---

## 5. Modelo de datos (tablas)

### `users`

`id`, `name`, `email` (único), `avatar`, `provider` (google|microsoft), `provider_id`,
**`publish_credits`** (int, créditos para publicar), `email_verified_at`, timestamps.

### `ads` (usa SoftDeletes → columna `deleted_at`)

`id`, `user_id` (FK→users, cascadeOnDelete), `category` (venta|compra|trabajo|busca),
`description` (≤144), `phone` (9 díg., empieza en 9), `coverage`
(nacional|departamental|provincial|distrital), `department`, `province`, `district`
(nullables según cobertura), `status` (active|inactive), `featured_until` (nullable, legacy),
`views` (int), timestamps, **`deleted_at`** (papelera). Índices: `status`, `category`,
`created_at`, `featured_until` + compuestos `(status,created_at)`, `(status,category,created_at)`,
`(status,department)`, `(coverage)`.

### `transactions` (soft deletes)

`id`, `user_id`, `ad_id` (nullable, legacy), `charge_id`, `order_number`, `payment_method`
(card|yape|pagoefectivo), `amount` (céntimos), `currency`, `status`
(paid|pending|failed|refunded), `culqi_response_code`, **`customer_email` (encriptado)**,
`customer_name`, `card_last4`, `card_brand`, `description`, `metadata` (JSON: order_id, plan…),
timestamps. Nunca guarda número de tarjeta ni CVV.

### `webhook_events` (auditoría + idempotencia)

`id`, `event_id` (único de Culqi), `type`, `resource_id`, `status`
(received|processed|ignored), `payload` (JSON saneado), `processed_at`, timestamps.

**Relaciones:** `User hasMany Ad`, `Ad belongsTo User`. Transacciones se ligan por `user_id`
y `metadata.order_id` / `charge_id`.

---

## 6. Flujos principales

### Registro / Login (OAuth)

`/auth/{provider}/redirect` → proveedor valida → `/auth/{provider}/callback` →
`User::updateOrCreate` (por email) → si **wasRecentlyCreated** → +9999 créditos → `Auth::login`
→ redirige a `/mis-anuncios`.

### Publicar anuncio

`/publicar` → ¿sesión? no → login · sí → ¿créditos? no → CTA `/pago` · sí → formulario →
`POST /anuncios` → validación + **moderación (NoProfanity)** → ¿ok? no → modal de aviso ·
ok → **transacción atómica** (decremento condicional de 1 crédito + crea anuncio) → 201.

### Compra de créditos (pago)

`/pago` → elige plan → `POST /pago/orden` (el **monto lo pone el backend** según el plan) →
abre widget Culqi → paga → `POST /pago/orden/confirmar` → ¿paid? →
**evento `PaymentConfirmed`** → `GrantCreditsOnPayment` (suma créditos) +
`NotifyAdminOnPayment` (correo). El **webhook** confirma asíncrono (idempotente).
Camino "No/rechazado" → muestra error y permite reintentar. Anti doble-pago: la orden ES el
pago (no se carga token aparte) + overlay que bloquea la pantalla.

### Eliminar / Papelera

`DELETE /anuncios/{id}` → **soft delete** (`deleted_at`). Pestaña **Papelera** en mis-anuncios
lista los eliminados ≤30 días con "se borra en X días" + **Restaurar**
(`PATCH /anuncios/{id}/restaurar`). El comando `ads:purge-trash` (cron diario) borra de verdad
los de +30 días. Para admin: `php artisan ads:trash`.

### Listado público (rendimiento)

`GET /api/publicaciones?cat&dep&prov&dist&q&page` → **filtra y pagina en la BD** (24/página, usa índices)
→ devuelve solo esa página + total + nº de páginas. La home tiene búsqueda con _debounce_ y
paginación con ventana. (Diagramas en `DIAGRAMAS.md`.)

---

## 7. Rutas / endpoints

### Páginas (navegador, vía PageController)

`GET /` · `GET /publicar` · `GET /mis-anuncios` · `GET /completar-perfil` · `GET /pago`

### Auth

`GET /auth/{provider}/redirect` · `GET /auth/{provider}/callback` · `GET /me` · `POST /logout`

### Anuncios

`GET /api/publicaciones` (público, paginado/filtrado) · `GET /mis-anuncios/datos` (paginado + conteos) ·
`GET /mis-anuncios/papelera` · `POST /anuncios` (publicar) · `PATCH /anuncios/{ad}`
(activar/desactivar) · `DELETE /anuncios/{ad}` (soft delete) · `PATCH /anuncios/{id}/restaurar`

### Pagos (web, con CSRF)

`POST /pago/orden` · `POST /pago/cargo` · `POST /pago/orden/confirmar` ·
`POST /pago/guardar-tarjeta` · `POST /pago/devolucion` (**solo admin**) ·
`POST /culqi/webhook` (sin CSRF, throttle 60/min)

### Otros

`GET /api/health` · `GET /up` (health Laravel). Rutas `/api/payment/*` y `/api/transaction/*`
**solo existen fuera de producción** (pruebas Postman).

---

## 8. Seguridad (ya implementada)

- **SQLi:** Eloquent/bindings; el `LIKE` escapa comodines `% _ \`.
- **XSS:** el texto de anuncios se devuelve escapado con `e()` en la API.
- **CSRF:** activo en web; solo `culqi/webhook` excluido.
- **Manipulación de precio:** el monto se resuelve del backend según el plan; `plan` validado
  contra `config('plans')`.
- **IDOR:** `ownsAd()` en editar/eliminar/restaurar; transacciones por `charge_id`.
- **Refunds solo-admin** (`EnsureAdmin` + `ADMIN_EMAILS`).
- **Rate limiting:** API 120/min, acciones 60/min, pagos 5–20/min, webhook 60/min (por usuario o IP).
- **CORS** restringido a tu dominio (`config/cors.php`), métodos/headers explícitos.
- **Cabeceras de seguridad** (`SecurityHeaders`): X-Frame-Options, nosniff, Referrer-Policy,
  Permissions-Policy, HSTS (sobre HTTPS).
- **Webhook:** idempotencia (`event_id`) + anti-spoofing (re-consulta a Culqi).
- **Secretos:** la `secret_key` de Culqi solo en backend; email encriptado en reposo; nunca se
  guarda tarjeta/CVV. `.env` fuera de git.
- **Pendiente de servidor:** `SESSION_SECURE_COOKIE=true` en prod + redirección HTTPS en `.htaccess`
  (ver `DEPLOY.md`).

---

## 9. Configuración (.env)

Claves: `APP_ENV` (production en prod), `APP_DEBUG` (false en prod), `APP_URL`,
`DB_*` (en prod `DB_HOST=localhost`, base `anuncial_culqi_bd`, usuario `anuncial_culqi`),
`CULQI_PUBLIC_KEY`/`CULQI_SECRET_KEY` (pk*test/sk_test en sandbox; pk_live/sk_live para cobrar),
`CULQI_RSA*_`(opcional, se activa solo),`GOOGLE*CLIENT*_`+`GOOGLE*REDIRECT_URI`,
`MICROSOFT*_`, `ADMIN*EMAILS`(correos admin / aviso de pago),`CORS_ALLOWED_ORIGINS`,
`MAIL*_`(con`log`no envía; SMTP de cPanel para correos reales),`PERSPECTIVE\_\*`(moderación IA).
Ver plantilla en`.env.example`.

---

## 10. Despliegue (cPanel)

- Flujo: **PC → GitHub → servidor**. Deploy: `git pull && bash deploy.sh`.
- `deploy.sh` corre composer, `migrate --force`, `optimize:clear`, `config:cache`, `route:cache`, permisos.
- **SSH:** `ssh anuncial@192.250.227.240` (usuario `anuncial`, NO `enlixpe`). También cPanel → Terminal.
- `.htaccess` en `public_html` redirige todo a `public/`. La home se sirve por Laravel
  (no como index.html estático) por el cache-busting.
- **Cron del scheduler** (para la purga de la papelera), cada minuto en cPanel → Cron Jobs:
  `/usr/local/bin/php /home/anuncial/public_html/artisan schedule:run >> /dev/null 2>&1`
- Detalle completo en `DEPLOY.md`.

---

## 11. Comandos artisan personalizados

```bash
php artisan ads:trash [--user= --restore=ID --purge=ID]  # admin de la papelera
php artisan ads:purge-trash [--days=30]                   # purga (lo corre el cron)
php artisan db:seed --class=AdsSeeder                      # anuncios de demo (cantidad en el seeder)
```

---

## 12. Estado actual y pendientes

**Hecho:** clasificados + login + publicar con créditos; pagos Culqi (sandbox); paginación
server-side; papelera; rediseño UI; seguridad; correo de aviso (código); migrado a cPanel nuevo.

**Pendientes:** configurar **SMTP** en prod (hoy `MAIL_MAILER=log` → no llegan correos);
configurar el **cron** del scheduler; **go-live** real de Culqi (llaves live + RSA + aprobación
comercial) y reactivar los botones de planes; aplicar `SESSION_SECURE_COOKIE` + redirección HTTPS;
rotar `GOOGLE_CLIENT_SECRET`; tests; (opcional) índice FULLTEXT para la búsqueda.

---

## 13. Datos de prueba (Culqi sandbox)

- **Tarjeta:** `4111 1111 1111 1111`, CVV `123`, exp `09/2028`.
- **Yape:** celular `999999999`, OTP `123456`.
- Mínimo para Yape/billeteras: S/ 6.00 (con menos solo sale tarjeta). Sandbox = sin dinero real.

---

## 14. Documentos relacionados

`README.md` (general) · `DEPLOY.md` (despliegue) · `DIAGRAMAS.md` (flujos Mermaid) ·
`PROGRESO.md` (bitácora) · `API.md` (API de pruebas, desactualizada).
