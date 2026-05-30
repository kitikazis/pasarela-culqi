# 📋 Progreso — Pasarela Culqi

> Última actualización: 2026-05-30. Estado: **sandbox (pruebas, sin dinero real)**.

---

## ✅ Lo que YA está hecho y funcionando

### Backend (Laravel 12 + SDK Culqi + RSA)

- **CulqiService** — charge, yape, refund, customer, card, order, token, ping
- **PaymentController** — showCheckout, charge, yape, refund, saveCard, createOrder, webhook, health
- **Transaction** (modelo) — email encriptado, soft deletes
- **Form Requests** — Charge, Yape, Refund, CreateOrder (con validación)
- **config/culqi.php** y **config/plans.php** (precios autoritativos en backend)
- **Rutas** web (`/pago/*` con CSRF) y API (`/api/payment/*` sin CSRF, para Postman)
- **Seguridad** — `.htaccess` con HTTPS + headers, CSRF, RSA auto-activable

### Métodos de pago probados

| Método                                   | Estado                                                              |
| ---------------------------------------- | ------------------------------------------------------------------- |
| 💳 Tarjeta                               | ✅ Cobra (probado: chr*test*...)                                    |
| 🔁 Devolución                            | ✅ Funciona                                                         |
| 📦 Crear orden (PagoEfectivo/billeteras) | ✅ Se genera la orden                                               |
| 💾 Guardar tarjeta                       | ✅ Código listo                                                     |
| 📱 Yape                                  | ⚠️ Código OK, falta habilitar teléfono de prueba en tu cuenta Culqi |

### Frontend

- Página `/pago` — **Custom Checkout multipago v1.0** con planes y precios
- Botón "Destacar — Planes" en `index.html`
- Planes: Básico S/6 · Plus S/25 · Premium S/50

### Git

- `main` = todo el trabajo (sincronizado con GitHub) ✅
- Respaldo del estado viejo en rama `backup-no-correr`
- Pendiente de push: commit de `.env.example` ordenado (corre `git push origin main`)

---

## 🔜 EN LO QUE NOS QUEDAMOS (retomar aquí)

Estábamos por implementar el **WEBHOOK** (para que PagoEfectivo/billeteras/Cuotéalo se confirmen solos).

**Ya hecho:**

- ✅ ngrok instalado en `C:\Users\luisk\ngrok\ngrok.exe` (v3.39.5)
- ✅ Token de ngrok configurado

**Pendiente (próximos pasos):**

```
1️⃣  Crear tabla webhook_events (idempotencia + auditoría)   ← lo hace Claude
2️⃣  Mejorar el webhook para usarla                          ← lo hace Claude
3️⃣  Levantar el túnel:  C:\Users\luisk\ngrok\ngrok.exe http 8000
4️⃣  Copiar la URL pública (https://xxxx.ngrok.io)
5️⃣  Registrarla en CulqiPanel → Desarrollo → Webhooks
6️⃣  Pagar un QR sandbox → verificar que la BD marque "paid"
```

---

## 🚀 Cómo retomar (comandos)

```powershell
# 1. Levantar el servidor Laravel
cd c:\Users\luisk\pasarela-culqi
php artisan serve --port=8000

# 2. (Para webhook) Levantar ngrok en otra terminal
C:\Users\luisk\ngrok\ngrok.exe http 8000

# 3. Abrir la pasarela
#    http://127.0.0.1:8000/pago
```

---

## 📝 Notas importantes

- **RSA está DESACTIVADO** (sandbox). En `.env`: `CULQI_RSA_ID` vacío. Para producción se ponen las llaves `rs_live` reales (una línea, entre comillas).
- **Tarjeta de prueba:** `4111 1111 1111 1111`, CVV `123`, exp `09/2028`.
- **Mínimo Culqi para órdenes/billeteras:** S/ 6.00 (por eso el Plan Básico está en S/6).
- **El QR de billeteras en sandbox NO mueve dinero real** — es simulación.

---

## 🗺️ Roadmap para terminar (orden sugerido)

```
1. Webhook + ngrok          → cierra PagoEfectivo/billeteras/Cuotéalo   ← SIGUIENTE
2. Conectar pago ↔ anuncio/usuario → que el pago active el plan de verdad
3. Habilitar teléfono Yape  → cierra Yape
4. Página de confirmación + idempotencia
5. (Producción) llaves live + RSA real + 3DS + aprobación de Culqi
```

---

## 📂 Tablas de BD

- **`transactions`** ✅ (la tienes) — cada pago/intento
- **`webhook_events`** 🔜 (la crearemos con el webhook) — idempotencia
- **`saved_cards`** ⏳ (solo si harás cobros one-click/recurrentes)
