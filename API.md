# Pasarela de Pagos Culqi — Guía de la API

Base URL: `http://127.0.0.1:8000`  
Servidor: `php artisan serve`

---

## Endpoints

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/health` | Estado de la BD y Culqi |
| POST | `/api/payment/charge` | Pago con tarjeta |
| POST | `/api/payment/yape` | Pago con Yape |
| GET | `/api/payment/{id}` | Consultar pago por UUID |

---

## 1. Health Check

```
GET /api/health
```

**Respuesta exitosa**
```json
{
  "status": "ok",
  "database": true,
  "culqi": {
    "ok": true,
    "http": 200,
    "message": "Credenciales válidas"
  },
  "timestamp": "2026-05-30T12:00:00+00:00"
}
```

---

## 2. Pago con Tarjeta

El pago con tarjeta requiere **2 pasos**:

### Paso 1 — Generar el token en Culqi (llave pública)

```
POST https://api.culqi.com/v2/tokens
Authorization: Bearer pk_test_uqJHboypE9phYJR8
Content-Type: application/json
```

```json
{
  "card_number": "4111111111111111",
  "cvv": "123",
  "expiration_month": "09",
  "expiration_year": "2025",
  "email": "test@culqi.com"
}
```

**Respuesta** → guarda el `id` que empieza con `tkn_test_...`

### Paso 2 — Crear el cargo en tu API (llave privada)

```
POST /api/payment/charge
Content-Type: application/json
Accept: application/json
```

```json
{
  "token_id": "tkn_test_XXXXXXXXXXXXXXXX",
  "amount": 1000,
  "currency": "PEN",
  "email": "test@culqi.com",
  "description": "Pago de prueba"
}
```

> `amount` en céntimos: `1000` = S/ 10.00

**Respuesta exitosa (200)**
```json
{
  "success": true,
  "message": "Pago procesado exitosamente.",
  "payment_id": "550e8400-e29b-41d4-a716-446655440000",
  "charge_id": "chr_test_XXXXXXXXXX",
  "amount": 10.00,
  "currency": "PEN"
}
```

**Error de validación (422)**
```json
{
  "success": false,
  "message": "El campo token de pago es obligatorio.",
  "errors": {
    "token_id": ["El campo token de pago es obligatorio."]
  }
}
```

**Error de Culqi (400)**
```json
{
  "success": false,
  "message": "La tarjeta fue declinada."
}
```

---

## 3. Pago con Yape

Un solo paso — tu backend crea el token internamente.

```
POST /api/payment/yape
Content-Type: application/json
Accept: application/json
```

```json
{
  "phone_number": "999999999",
  "otp": "012345",
  "amount": 1000,
  "email": "test@culqi.com",
  "description": "Pago con Yape"
}
```

**Respuesta exitosa (200)**
```json
{
  "success": true,
  "message": "Pago procesado exitosamente.",
  "payment_id": "550e8400-e29b-41d4-a716-446655440000",
  "charge_id": "chr_test_XXXXXXXXXX",
  "amount": 10.00,
  "currency": "PEN"
}
```

**Error indicando el paso donde falló**
```json
{
  "success": false,
  "message": "Número de teléfono no registrado en Yape.",
  "failed_step": "token_yape"
}
```

---

## 4. Consultar Pago

```
GET /api/payment/{payment_id}
Accept: application/json
```

**Respuesta (200)**
```json
{
  "success": true,
  "payment_id": "550e8400-e29b-41d4-a716-446655440000",
  "charge_id": "chr_test_XXXXXXXXXX",
  "amount": 10.00,
  "currency": "PEN",
  "status": "paid",
  "payment_method": "card",
  "email": "test@culqi.com",
  "description": "Pago de prueba",
  "created_at": "2026-05-30T12:00:00.000000Z"
}
```

**Estados posibles:** `pending` · `paid` · `failed`

---

## Datos de prueba — Sandbox Culqi

### Tarjetas

| Marca | Número | CVV | Exp | Resultado |
|-------|--------|-----|-----|-----------|
| Visa | `4111 1111 1111 1111` | `123` | `09/2025` | ✅ Aprobada |
| Mastercard | `5111 1111 1111 1118` | `123` | `09/2025` | ✅ Aprobada |
| Visa | `4000 0000 0000 0002` | `123` | `09/2025` | ❌ Rechazada |

Más tarjetas: https://docs.culqi.com/es/documentacion/pagos-online/tarjetas-de-prueba

### Yape

| Campo | Valor |
|-------|-------|
| `phone_number` | `999999999` |
| `otp` | `012345` |

---

## Cómo importar en Postman

1. Abre Postman → **Import**
2. Selecciona el archivo `postman_collection.json` (ver abajo)
3. La variable `{{base_url}}` ya apunta a `http://127.0.0.1:8000`

---

## Flujo completo

```
Tarjeta:
  Postman → Culqi /v2/tokens (pk pública) → tkn_test_...
          → tu API POST /api/payment/charge → chr_test_...

Yape:
  Postman → tu API POST /api/payment/yape
          → internamente: Culqi /tokens + Culqi /charges
          → chr_test_...
```
