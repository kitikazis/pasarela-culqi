# Pasarela de Pagos Culqi

Pasarela de pagos construida en **Laravel 12** integrando **Culqi API v2.0** (Custom Checkout v4 multipago). Soporta pago con tarjeta, Yape, billeteras digitales, PagoEfectivo y Cuotéalo, con cobro por **planes** y precios definidos en el backend.

---

## Características

- 💳 **Tarjeta** — tokenización en el frontend (Culqi.js), cargo en el backend
- 📱 **Yape** — token + cargo en un solo paso
- 👛 **Multipago** — billeteras, banca móvil, agente, PagoEfectivo, Cuotéalo (vía órdenes)
- 📦 **Planes** — precios autoritativos en el backend (el cliente no puede manipular el monto)
- 🔒 **Encriptación RSA** — se activa automáticamente cuando hay llaves RSA en `.env`
- 🪝 **Webhook** — valida los eventos re-consultando el recurso a Culqi (anti-spoofing)
- 🗄️ **Auditoría** — cada intento se guarda en `transactions` (email encriptado en reposo)

---

## Requisitos

- PHP 8.2+
- MySQL
- Composer
- Cuenta Culqi (sandbox o producción)

---

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

---

## Variables de entorno (`.env`)

```env
CULQI_PUBLIC_KEY=pk_test_xxxxxxxxxxxx      # frontend
CULQI_SECRET_KEY=sk_test_xxxxxxxxxxxx      # backend (nunca exponer)
CULQI_BASE_URL=https://api.culqi.com/v2

# Encriptación RSA — solo producción. Una sola línea, entre comillas.
CULQI_RSA_ID=
CULQI_RSA_PUBLIC_KEY=
```

> La RSA se activa sola cuando `CULQI_RSA_ID` y `CULQI_RSA_PUBLIC_KEY` tienen valores reales.
> En sandbox déjalas vacías.

---

## Endpoints

### Web (con CSRF, para el navegador)

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET  | `/pago` | Página de planes + Custom Checkout |
| POST | `/pago/cargo` | Cargo con tarjeta |
| POST | `/pago/orden` | Crea orden (PagoEfectivo / Cuotéalo) |
| POST | `/pago/devolucion` | Devolución |
| POST | `/pago/guardar-tarjeta` | Guarda cliente + tarjeta (one-click) |
| POST | `/culqi/webhook` | Notificaciones de Culqi |

### API (sin CSRF, para pruebas / servidor-a-servidor)

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET  | `/api/health` | Estado de BD + Culqi |
| POST | `/api/payment/charge` | Cargo con tarjeta |
| POST | `/api/payment/yape` | Pago con Yape |
| POST | `/api/payment/refund` | Devolución |
| POST | `/api/payment/save-card` | Guardar tarjeta |
| POST | `/api/payment/order` | Crear orden |
| GET  | `/api/transaction/{id}` | Consultar transacción |

---

## Planes

Definidos en [`config/plans.php`](config/plans.php) (precio en céntimos):

| Plan | Precio | Duración |
|------|--------|----------|
| Básico  | S/ 6.00  | 7 días  |
| Plus    | S/ 25.00 | 30 días |
| Premium | S/ 50.00 | 90 días |

> El frontend envía solo el **id del plan**; el backend resuelve el precio.
> El mínimo de Culqi para órdenes/billeteras es **S/ 6.00**.

---

## Datos de prueba (sandbox)

**Tarjetas:**

| Marca | Número | CVV | Exp | Resultado |
|-------|--------|-----|-----|-----------|
| Visa | `4111 1111 1111 1111` | 123 | 09/2028 | ✅ Aprobada |
| Visa | `4000 0000 0000 0002` | 123 | 09/2028 | ❌ Rechazada |

Más info en [`API.md`](API.md). Colección Postman en [`postman_collection.json`](postman_collection.json).

---

## Arquitectura

```
app/
├── Http/Controllers/PaymentController.php   # showCheckout, charge, yape, refund, saveCard, createOrder, webhook, health
├── Http/Requests/                           # ChargeRequest, YapeRequest, RefundRequest, CreateOrderRequest
├── Services/CulqiService.php                # SDK culqi/culqi-php + RSA
└── Models/Transaction.php                   # email encriptado, soft deletes
config/culqi.php · config/plans.php
resources/views/payment/checkout.blade.php   # Custom Checkout multipago v1.0
```

---

## Producción (cPanel)

1. Cuenta Culqi aprobada + métodos de pago activados por Culqi
2. Llaves `pk_live_` / `sk_live_` + RSA real (`rs_live_`) en `.env`
3. `APP_ENV=production`, `APP_DEBUG=false`, HTTPS/SSL
4. Webhook configurado en el panel de Culqi
5. `composer install --no-dev --optimize-autoloader && php artisan migrate --force`

> 📘 Despliegue paso a paso (SSH + Git + cPanel) y cómo subir cambios: [`DEPLOY.md`](DEPLOY.md)
