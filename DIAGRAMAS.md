# 🔁 Diagramas de flujo — anuncialo.pe

> Diagramas en **Mermaid**. Se renderizan automáticamente al abrir este archivo en
> **GitHub**. También puedes verlos/editarlos en [mermaid.live](https://mermaid.live)
> o en VS Code con la extensión _Markdown Preview Mermaid Support_.

---

## 1. Registro / Login (OAuth)

```mermaid
flowchart TD
    A["Usuario"] -->|"Ingresar con Google / Microsoft"| B["GET /auth/:provider/redirect"]
    B --> C["El proveedor valida la identidad"]
    C --> D["GET /auth/:provider/callback"]
    D --> E{"¿Cuenta nueva?"}
    E -->|"Sí"| F["Crea usuario + 20 créditos gratis"]
    E -->|"No"| G["Actualiza datos del usuario"]
    F --> H["Inicia sesión"]
    G --> H
    H --> I["Redirige a Mis anuncios"]
```

---

## 2. Publicar un anuncio

```mermaid
flowchart TD
    A["Usuario en /publicar"] --> B{"¿Tiene sesión?"}
    B -->|"No"| C["Pide iniciar sesión"]
    B -->|"Sí"| D{"¿Tiene créditos?"}
    D -->|"No"| E["CTA: comprar un plan → /pago"]
    D -->|"Sí"| F["Llena el formulario"]
    F --> G["POST /anuncios"]
    G --> H{"Moderación de contenido"}
    H -->|"Rechazado"| I["Modal: contenido no permitido"]
    H -->|"OK"| J["Transacción atómica:<br/>gasta 1 crédito + crea anuncio"]
    J --> K["Anuncio publicado → Mis anuncios"]
```

---

## 3. Compra de créditos (pago con Culqi)

```mermaid
flowchart TD
    A["/pago: elige plan"] --> B["POST /pago/orden"]
    B --> C["Abre el widget de Culqi"]
    C --> D["Usuario paga<br/>(tarjeta / Yape / orden)"]
    D --> E["POST /pago/orden/confirmar"]
    E --> F{"¿Pagado?"}
    F -->|"Sí"| G["Evento PaymentConfirmed"]
    F -->|"Pendiente"| J["Orden generada<br/>(se confirma por webhook)"]
    F -->|"No / rechazado"| M["Muestra error<br/>(permite reintentar)"]
    G --> H["GrantCreditsOnPayment:<br/>suma créditos al usuario"]
    G --> I["NotifyAdminOnPayment:<br/>correo al administrador"]

    K["Culqi → POST /culqi/webhook"] --> L["ProcessCulqiWebhook:<br/>idempotencia + anti-spoofing"]
    L --> G
```

---

## 4. Eliminar y Papelera (soft delete)

```mermaid
flowchart TD
    A["Usuario elimina un anuncio"] --> B["DELETE /anuncios/:id"]
    B --> C["Soft delete:<br/>deleted_at = ahora"]
    C --> D["Aparece en la Papelera"]
    D --> E{"¿Dentro de 30 días?"}
    E -->|"Restaurar"| F["PATCH /anuncios/:id/restaurar<br/>deleted_at = null"]
    E -->|"Pasan 30 días"| G["Cron diario → ads:purge-trash"]
    F --> H["Vuelve a Mis anuncios"]
    G --> I["forceDelete:<br/>borrado definitivo"]
```

---

## 5. Listado público (paginado en el servidor)

```mermaid
flowchart TD
    A["Home: filtros / búsqueda / página"] --> B["GET /api/ads?cat&dep&prov&dist&q&page"]
    B --> C["AdController@index"]
    C --> D["Filtra en la BD (status=active + filtros)<br/>usa índices compuestos"]
    D --> E["Pagina (24 por página)"]
    E --> F["Devuelve solo esa página + total + nº de páginas"]
    F --> G["La home dibuja 24 tarjetas + paginación"]
```
