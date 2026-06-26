# Anuncialo.pe — Clasificados + Pasarela Culqi

Plataforma de **anuncios clasificados** para Perú con **pasarela de pagos Culqi** integrada. Los usuarios inician sesión, **compran un plan** (paquete de créditos) y **publican anuncios** gastando 1 crédito por publicación. Construida en **Laravel 12**.

> **Modelo de negocio:** publicar un anuncio gasta **1 crédito de publicación**. Cada usuario nuevo recibe **20 créditos gratis al registrarse**. Comprar un plan suma más créditos (no vencen).
>
> **Estado actual:** los botones de compra de planes están **ocultos por ahora** (los usuarios viven de los 20 créditos gratis). El checkout y el flujo de pago siguen **funcionando por dentro**; reactivarlos es descomentar los botones.

---

## Tabla de contenido

1. [Qué se puede hacer](#qué-se-puede-hacer)
2. [Cómo funciona (flujos)](#cómo-funciona-flujos)
3. [Stack tecnológico](#stack-tecnológico)
4. [Arquitectura](#arquitectura)
5. [Planes y créditos](#planes-y-créditos)
6. [Seguridad](#seguridad)
7. [Rutas / Endpoints](#rutas--endpoints)
8. [Instalación local](#instalación-local)
9. [Variables de entorno](#variables-de-entorno)
10. [Despliegue (cPanel)](#despliegue-cpanel)
11. [Datos de prueba (Culqi sandbox)](#datos-de-prueba-culqi-sandbox)
12. [Mantenimiento / tareas comunes](#mantenimiento--tareas-comunes)
13. [Otros documentos](#otros-documentos)

---

## Qué se puede hacer

### Como visitante (sin cuenta)
- Ver el **listado público de anuncios** activos.
- **Filtrar** por categoría (Venta, Compra, Empleo, Busco), por ubicación (Departamento → Provincia → Distrito, o **Nacional**) y **buscar** por texto.
- Ver el detalle de cada anuncio (descripción, ubicación, teléfono, fecha).

### Como usuario registrado
- **Iniciar sesión** con Google o Microsoft (OAuth, sin contraseñas).
- **20 créditos gratis** al crear la cuenta (regalo de bienvenida).
- **Comprar un plan** (paquete de créditos) pagando con **tarjeta, Yape, billeteras, banca móvil, agente, PagoEfectivo o Cuotéalo**. *(Botones ocultos por ahora; el flujo existe.)*
- **Publicar anuncios** (gasta 1 crédito por anuncio). Incluye categoría, descripción (máx. 144 caracteres), teléfono y cobertura geográfica.
- **Gestionar "Mis anuncios":** activar / desactivar / eliminar, ver vistas y saldo de créditos. La lista está **paginada** (escala a miles de anuncios).
- **Papelera:** al eliminar, el anuncio no se borra de verdad (*soft delete*); va a la **Papelera**, donde se puede **restaurar dentro de 30 días**. Después se borra solo.

### Moderación automática
- Al publicar, el contenido pasa por un **filtro de moderación** (groserías + servicios para adultos) por listas locales, con **IA opcional (Google Perspective)**. Si el texto no se permite, aparece un **modal de advertencia** y no se publica.

### Como administrador
- **Aviso por correo** cada vez que se confirma un pago (a los correos de `ADMIN_EMAILS`).
- **Devoluciones (refunds)** de pagos — restringido a los correos definidos en `ADMIN_EMAILS`.
- **Control de la Papelera** desde el terminal: `php artisan ads:trash` (listar / restaurar / purgar). Los anuncios eliminados quedan en BD (`onlyTrashed()`) listos para un futuro panel admin.

---

## Cómo funciona (flujos)

### Registro (regalo de bienvenida)
```
Login OAuth (Google/Microsoft) → si la cuenta es NUEVA (wasRecentlyCreated)
   → AuthController le asigna 20 créditos gratis (constante WELCOME_CREDITS)
```

### Comprar créditos
```
Usuario logueado → /pago → elige plan → paga con Culqi (tarjeta/Yape/orden)
   → pago confirmado → evento PaymentConfirmed
   → GrantCreditsOnPayment suma los créditos del plan al usuario
   → NotifyAdminOnPayment envía un correo de aviso a ADMIN_EMAILS
```

### Publicar un anuncio
```
/publicar → (si tiene créditos) llena el formulario → POST /anuncios
   → moderación (NoProfanity) → gasta 1 crédito (atómico) → anuncio creado
   → (si NO tiene créditos) → bloqueado con CTA "Comprar un plan"
```

### Pago (anti doble cobro)
```
Checkout → crea orden (habilita PagoEfectivo/Cuotéalo) → abre widget Culqi
   → al confirmar: overlay "Procesando…" → check verde animado
   → cierra el modal de Culqi + botón "✓ Pago realizado" (no se paga 2 veces)
```

### Webhook (confirmación asíncrona)
```
Culqi → POST /culqi/webhook → ProcessCulqiWebhook:
   idempotencia (event_id) + anti-spoofing (re-consulta el recurso a Culqi)
   → marca la transacción como pagada → dispara créditos una sola vez
```

---

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | **Laravel 12**, PHP 8.2+ |
| Base de datos | MySQL |
| Pagos | **Culqi** API v2.0 — Custom Checkout v4 (multipago) |
| Auth | Laravel **Socialite** (Google + Microsoft) |
| Frontend público | HTML/CSS/JS estático servido por Laravel (con cache-busting automático) |
| Frontend checkout | Vista **Blade** (`payment/checkout.blade.php`) |
| Moderación IA (opcional) | Google Perspective API |

---

## Arquitectura

Organizada por responsabilidades (no todo en los controladores):

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AdController.php          # anuncios: listar, publicar, activar/eliminar
│   │   ├── AuthController.php        # login social, /me, logout
│   │   ├── PageController.php        # sirve las páginas HTML con cache-busting
│   │   ├── PaymentController.php     # checkout, cargo, orden, refund, webhook
│   │   └── HealthController.php      # estado de BD + Culqi
│   ├── Requests/                     # validación (FormRequests) por endpoint
│   │   ├── StoreAdRequest.php        # incluye regla NoProfanity, máx 144
│   │   ├── ChargeRequest / CreateOrderRequest / YapeRequest / RefundRequest
│   │   ├── SaveCardRequest / ConfirmOrderRequest
│   └── Middleware/EnsureAdmin.php    # protege acciones de admin (refund)
├── Actions/
│   ├── ProcessCulqiWebhook.php       # idempotencia + anti-spoofing + auditoría
│   └── RecordTransaction.php         # persistencia única de transacciones (atómica)
├── Services/
│   ├── CulqiService.php              # capa sobre el SDK culqi/culqi-php + RSA
│   └── ContentModerator.php          # moderación (listas + Perspective IA)
├── Events/PaymentConfirmed.php       # se dispara al confirmar un pago
├── Listeners/
│   ├── GrantCreditsOnPayment.php     # suma créditos al usuario
│   └── NotifyAdminOnPayment.php      # avisa por correo a ADMIN_EMAILS
├── Mail/PaymentReceivedMail.php      # correo de aviso de pago (Mailable)
├── Console/Commands/                 # tareas de admin (papelera de anuncios)
│   ├── PurgeTrashedAds.php           # purga los eliminados de +30 días (cron diario)
│   └── TrashAds.php                  # ads:trash → listar / restaurar / purgar
├── Rules/NoProfanity.php             # regla de validación de contenido
├── Models/Ad.php · User.php · Transaction.php · WebhookEvent.php
└── Providers/AppServiceProvider.php  # eventos + rate limiters

config/  plans.php · culqi.php · moderation.php · cors.php · app.php
routes/  web.php (navegador, con CSRF) · api.php (API)
resources/views/payment/checkout.blade.php · emails/payment-received.blade.php
public/  index.html · publicar.html · mis-anuncios.html · completar-perfil.html · styles.css · peru-data.js
database/migrations/
```

> **Diseño:** el frontend usa un design system con paleta única (azul/ámbar/verde),
> tipografía **Inter** e iconos **Lucide** (sin Font Awesome ni emojis). El registro
> de usuarios nuevos otorga **20 créditos** vía `AuthController` (`WELCOME_CREDITS`).

**Patrones clave:**
- **Config-driven:** planes, moderación, CORS y admins se ajustan en `config/` o `.env` sin tocar código.
- **FormRequests** para toda validación de entrada.
- **Services/Actions** para lógica reutilizable (Culqi, webhook, transacciones).
- **Events/Listeners** como "costura" de negocio (pago → créditos).

---

## Planes y créditos

Definidos en [`config/plans.php`](config/plans.php) (precio en céntimos). **El frontend envía solo el `id` del plan; el backend resuelve el precio** (no se puede manipular el monto).

| Plan | Precio | Créditos |
|------|--------|----------|
| Básico | S/ 1.00 | 1 publicación |
| Plus | S/ 25.00 | 10 publicaciones |
| Premium | S/ 50.00 | 30 publicaciones |

> Ajusta `credits` y `amount` libremente; todo el UI los lee de ahí.
> El mínimo de Culqi para órdenes/billeteras/Yape es **S/ 6.00** (600 céntimos).

---

## Seguridad

Defensa en capas, ya implementada:

| Capa | Qué protege |
|------|-------------|
| **CORS** (`config/cors.php`) | Solo tu dominio puede llamar la API desde el navegador |
| **CSRF** | Peticiones falsas usando la sesión del usuario |
| **Auth + propiedad** | Cada acción exige sesión y opera solo sobre datos propios |
| **Rate limiting por usuario** | 120 req/min (API) y 60 req/min (acciones); pagos 5–20/min |
| **Precio server-side** | El monto se resuelve del plan en el backend |
| **XSS** | El texto de anuncios se devuelve **escapado** (`e()`) en la API |
| **SQL Injection** | Prepared statements de Eloquent (sin SQL concatenado) |
| **Refund solo-admin** | Middleware `EnsureAdmin` + `ADMIN_EMAILS` |
| **IDOR** | Transacciones se consultan por `charge_id` (no por id enumerable) |
| **Moderación** | Bloquea groserías/contenido adulto al publicar |
| **Webhook** | Idempotencia + anti-spoofing (re-consulta a Culqi) |
| **Datos sensibles** | Email encriptado en reposo; **nunca** se guardan tarjeta/CVV/llaves |
| **Créditos atómicos** | Gastar crédito + crear anuncio en una transacción de BD |

> Las rutas de prueba `/api/payment/*` y `/api/transaction/*` **solo se registran fuera de producción**.

---

## Rutas / Endpoints

### Páginas (navegador)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/` | Listado público de anuncios |
| GET | `/publicar` | Formulario para publicar (requiere créditos) |
| GET | `/mis-anuncios` | Gestión de tus anuncios |
| GET | `/pago` | Comprar un plan (checkout Culqi) |
| GET | `/completar-perfil` | Perfil del usuario |

### Autenticación
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/auth/{google\|microsoft}/redirect` | Inicia login OAuth |
| GET | `/auth/{provider}/callback` | Callback OAuth |
| GET | `/me` | Usuario actual + créditos |
| POST | `/logout` | Cerrar sesión |

### Anuncios
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/publicaciones` | Lista pública (JSON) — **paginada y filtrada en servidor** (`cat`, `dep`, `prov`, `dist`, `q`, `page`). _No se llama `/api/ads` a propósito: los bloqueadores (Brave/uBlock) cortan URLs con "ads"._ |
| GET | `/mis-anuncios/datos` | Anuncios del usuario (paginado + conteos; `status`, `page`) |
| GET | `/mis-anuncios/papelera` | Papelera del usuario (eliminados ≤ 30 días) |
| POST | `/anuncios` | Publicar (gasta 1 crédito) |
| PATCH | `/anuncios/{ad}` | Activar / desactivar (propio) |
| DELETE | `/anuncios/{ad}` | Eliminar → soft delete (Papelera) |
| PATCH | `/anuncios/{id}/restaurar` | Restaurar un anuncio de la Papelera |

### Pagos (web, con CSRF)
| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/pago/orden` | Crear orden (PagoEfectivo/Cuotéalo) — requiere sesión |
| POST | `/pago/cargo` | Cargo con tarjeta — requiere sesión |
| POST | `/pago/orden/confirmar` | Verificar estado de la orden |
| POST | `/pago/guardar-tarjeta` | Guardar cliente+tarjeta (one-click) |
| POST | `/pago/devolucion` | Devolución — **solo admin** |
| POST | `/culqi/webhook` | Notificaciones de Culqi (sin CSRF) |

### Salud
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/health` | Estado de BD + Culqi (oculta detalles salvo `APP_DEBUG`) |

> La API de prueba `/api/payment/*` se documenta en [`API.md`](API.md) (solo no-producción).

---

## Instalación local

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configura la BD y las llaves de Culqi en .env
php artisan migrate
php artisan serve            # http://127.0.0.1:8000
```

Requisitos: **PHP 8.2+**, **MySQL**, **Composer**, cuenta Culqi (sandbox o producción).

---

## Variables de entorno

```env
APP_NAME="Anuncialo"
APP_ENV=local                  # production en el servidor
APP_DEBUG=true                 # false en producción
APP_URL=http://127.0.0.1:8000

# Base de datos (MySQL)
DB_DATABASE=...  DB_USERNAME=...  DB_PASSWORD=...

# Culqi
CULQI_PUBLIC_KEY=pk_test_xxxx          # frontend (tokeniza la tarjeta)
CULQI_SECRET_KEY=sk_test_xxxx          # backend (NUNCA exponer)
CULQI_RSA_ID=                          # solo producción (se activa solo)
CULQI_RSA_PUBLIC_KEY=

# Login social
GOOGLE_CLIENT_ID=...  GOOGLE_CLIENT_SECRET=...
MICROSOFT_CLIENT_ID=...  MICROSOFT_CLIENT_SECRET=...

# Administradores (devoluciones), separados por comas
ADMIN_EMAILS="tu@correo.com"

# CORS — orígenes permitidos (en producción solo tu dominio)
CORS_ALLOWED_ORIGINS="https://anuncialo.pe,https://www.anuncialo.pe"

# Moderación IA (opcional)
PERSPECTIVE_ENABLED=false
PERSPECTIVE_API_KEY=

# Correo — con "log" NO se envía (solo storage/logs). Para recibir avisos de pago,
# pon SMTP real (ej. cPanel). Detalle en DEPLOY.md.
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@anuncialo.pe"
MAIL_FROM_NAME="Anuncialo"
```

---

## Despliegue (cPanel)

El proyecto incluye [`deploy.sh`](deploy.sh) que automatiza todo en cPanel:

```bash
# Primera vez (dentro de ~/public_html, ya clonado):
bash deploy.sh        # crea .env y se detiene para que pongas credenciales
nano .env             # configura APP_ENV=production, BD, Culqi live, ADMIN_EMAILS, CORS...
bash deploy.sh        # instala, migra, cachea y deja listo

# Día a día (subir cambios):
git pull && bash deploy.sh
```

`deploy.sh` corre: `composer install --no-dev`, `migrate --force`, `optimize:clear`, `config:cache`, `route:cache` y ajusta permisos.

> Guía detallada (Git + SSH + cPanel): [`DEPLOY.md`](DEPLOY.md).

**Checklist de producción:**
- `APP_ENV=production`, `APP_DEBUG=false`, HTTPS/SSL activo.
- Llaves `pk_live_` / `sk_live_` + RSA (`rs_live_`) reales.
- `ADMIN_EMAILS` con tu correo (sin esto, nadie puede hacer devoluciones **ni llegan los avisos de pago**).
- `CORS_ALLOWED_ORIGINS` solo con tu dominio real.
- **SMTP real** (`MAIL_MAILER=smtp` + buzón de cPanel) para recibir los avisos de pago.
- Webhook configurado en el panel de Culqi apuntando a `https://tudominio/culqi/webhook`.

---

## Datos de prueba (Culqi sandbox)

**Tarjetas:**

| Marca | Número | CVV | Exp | Resultado |
|-------|--------|-----|-----|-----------|
| Visa | `4111 1111 1111 1111` | 123 | 09/28 | ✅ Aprobada |
| Mastercard | `5111 1111 1111 1118` | 123 | 09/28 | ✅ Aprobada |
| Visa | `4000 0000 0000 0002` | 123 | 09/28 | ❌ Rechazada |

**Yape (sandbox):** teléfono `900000001`, OTP `123456`. (El `999999999` NO está habilitado para Yape en sandbox.) Monto mínimo de Yape: **S/ 6.00** (usa Plan Plus o Premium).

Más: https://docs.culqi.com/es/documentacion/pagos-online/tarjetas-de-prueba

---

## Mantenimiento / tareas comunes

**Dar créditos a un usuario manualmente** (ej. para pruebas):
```bash
php artisan tinker --execute="App\Models\User::where('email','x@y.com')->first()->update(['publish_credits'=>5]);"
```

**Hacer admin a alguien:** agrega su correo a `ADMIN_EMAILS` en `.env` y corre `php artisan config:cache`.

**Cambiar precios o créditos de los planes:** edita [`config/plans.php`](config/plans.php) (todo el UI los lee de ahí).

**Editar palabras bloqueadas:** edita los arrays en [`config/moderation.php`](config/moderation.php).

**Activar la IA de moderación:** `PERSPECTIVE_ENABLED=true` + `PERSPECTIVE_API_KEY=...` en `.env`.

**Papelera de anuncios (soft delete):**
```bash
php artisan ads:trash                 # lista los eliminados (con días restantes)
php artisan ads:trash --restore=ID    # restaura uno
php artisan ads:trash --purge=ID      # lo borra definitivamente
php artisan ads:purge-trash           # borra todos los de +30 días (lo hace el cron diario)
```
> La purga automática necesita el cron del scheduler en cPanel — ver [`DEPLOY.md`](DEPLOY.md).

**Poblar anuncios de demo:** `php artisan db:seed --class=AdsSeeder` (la cantidad se ajusta en el seeder).

---

## Otros documentos

- [`API.md`](API.md) — Referencia de la API de prueba (Postman, solo no-producción).
- [`CONTEXTO.md`](CONTEXTO.md) — Contexto completo del proyecto en un solo archivo (ideal para dárselo a una IA).
- [`DEPLOY.md`](DEPLOY.md) — Despliegue paso a paso en cPanel.
- [`DIAGRAMAS.md`](DIAGRAMAS.md) — Diagramas de flujo (login, publicar, pago, papelera, listado).
- [`PROGRESO.md`](PROGRESO.md) — Bitácora de avance del proyecto.
