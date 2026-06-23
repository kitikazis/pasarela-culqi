# Despliegue en cPanel (anuncialo.pe)

Guía de cómo está desplegado el proyecto y cómo subir cambios a producción.

---

## Cómo está conectado todo

El proyecto vive en **tres lugares**. Tu PC y el servidor **no se hablan directamente**:
ambos pasan por **GitHub**, que actúa de intermediario.

```
┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│   1. TU PC      │  push   │   2. GITHUB     │  pull   │  3. SERVIDOR    │
│  (local)        │ ──────► │  (la nube)      │ ──────► │  cPanel/enlix   │
│ pasarela-culqi  │ ◄────── │ kitikazis/      │         │ ~/public_html   │
│  donde editas   │  clone  │ pasarela-culqi  │         │ donde se ve web │
└─────────────────┘         └─────────────────┘         └─────────────────┘
```

- **`git push`** — tu PC sube los cambios a GitHub (el "almacén central").
- **`git pull`** — el servidor baja la última versión desde GitHub.
- **SSH** — es solo la _puerta_ para entrar al servidor y dar las órdenes (`git pull`, etc.).
  No "conecta" nada por sí mismo; es tu acceso a la línea de comandos del servidor.
- **`git remote`** — lo que hace que `~/public_html` sepa _de dónde_ bajar los cambios.

---

## Particularidades del servidor

### Document Root → carpeta `public/`

El dominio principal `anuncialo.pe` apunta a `/public_html` (no se puede cambiar en el
dominio principal de cPanel). Como el `index.php` de Laravel vive en `public/`, hay un
**`.htaccess` en la raíz de `public_html`** que redirige todo el tráfico hacia `public/`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

> Este `.htaccess` **no está en Git** (es específico del servidor). No lo borres.

### Archivos que NO están en Git (viven solo en el servidor)

- **`.env`** — configuración de producción (credenciales, `APP_URL=https://anuncialo.pe`, etc.).
- **`vendor/`** — se genera con `composer install`.
- **`public_html/.htaccess`** — el redirector de arriba.

Por eso un `git pull` / `git reset --hard` **nunca pisa** estos archivos.

---

## Primera vez: conectar el servidor con GitHub

Solo se hace **una vez**. Por SSH:

```bash
cd ~/public_html
git init
git remote add origin https://github.com/kitikazis/pasarela-culqi.git
git fetch origin
git reset --hard origin/main      # NO toca .env, vendor/ ni .htaccess (están en .gitignore)
php artisan optimize:clear
chmod -R 775 storage bootstrap/cache
```

> Si `git fetch` pide usuario/contraseña, el repo es privado: genera un
> **Personal Access Token** en GitHub y úsalo como contraseña.

---

> 🔐 **Credenciales del servidor (usuario y contraseña SSH/cPanel):** están en el archivo
> local `CREDENCIALES.local.md`, que está en `.gitignore` y **no se sube** a git.

## Conectarse al servidor por SSH

Desde PowerShell / Git CMD en tu PC:

```bash
ssh anuncial@192.250.227.240
```

- **Usuario:** `anuncial` · **Host:** `192.250.227.240` · **Puerto:** 22 (por defecto).
- Te pedirá la **contraseña de esa cuenta** (al escribirla no se ve nada; es normal).
- La primera vez pregunta `Are you sure you want to continue connecting?` → escribe **`yes`**.

> ⚠️ El usuario es **`anuncial`**, NO `enlixpe` (ese es el de la base de datos).
> Si prefieres no usar la terminal local, también puedes entrar por **cPanel → Terminal**.

Una vez dentro, despliega:

```bash
cd public_html
git pull && bash deploy.sh
```

---

## Día a día: subir cambios a producción

**En tu PC** (editas y subes a GitHub):

```bash
git add .
git commit -m "lo que cambié"
git push origin main
```

**En el servidor** (por SSH, bajas los cambios):

```bash
cd ~/public_html && git pull origin main && bash deploy.sh
```

[`deploy.sh`](deploy.sh) corre `composer install --no-dev`, `migrate --force`,
`optimize:clear`, `config:cache`, `route:cache` y ajusta permisos. Listo: recarga
`anuncialo.pe` y verás los cambios.

> Si solo cambiaste HTML/CSS/JS (sin tocar PHP ni migraciones), basta con
> `git pull` — el cache-busting de assets se encarga de que el usuario vea lo último.

---

## Login social (Google OAuth)

El `redirect_uri` debe estar autorizado en
[Google Cloud Console](https://console.cloud.google.com) → _APIs y servicios → Credenciales_.
Cada entorno usa su propia URL (debe coincidir **exacta**: sin barra final, con/sin `https`):

| Entorno    | Redirect URI autorizado                      | JS Origin               |
| ---------- | -------------------------------------------- | ----------------------- |
| Producción | `https://anuncialo.pe/auth/google/callback`  | `https://anuncialo.pe`  |
| Local      | `http://localhost:8000/auth/google/callback` | `http://localhost:8000` |

> El valor lo toma de `GOOGLE_REDIRECT_URI` en el `.env` de cada entorno.
> Tras editar en Google Cloud, puede tardar de 5 min a 1 h en aplicarse.

---

## Montos mínimos de Culqi (métodos de pago)

Culqi **oculta** métodos cuyo mínimo no alcanza el monto del plan:

| Monto del plan | Métodos visibles                  |
| -------------- | --------------------------------- |
| S/ 1.00 – 5.99 | Solo 💳 Tarjeta                   |
| Desde S/ 6.00  | Tarjeta + 📱 Yape + 👛 Billeteras |

> Para que aparezcan **todos** los métodos en producción, usa **S/ 6.00 o más**
> en [`config/plans.php`](config/plans.php). S/ 1.00 sirve solo para probar con tarjeta.

---

## Checklist del `.env` de producción

El `.env` del servidor (en `~/public_html/.env`) **NO** está en Git: edítalo a mano.
Debe tener, como mínimo:

```env
APP_NAME="Anuncialo"
APP_ENV=production
APP_DEBUG=false                 # ¡nunca true en producción!
APP_URL=https://anuncialo.pe

# Base de datos (en cPanel la BD está en el MISMO servidor)
DB_HOST=localhost
DB_DATABASE=...  DB_USERNAME=...  DB_PASSWORD=...

# Culqi — para cobrar de verdad, llaves LIVE (pk_live_/sk_live_)
CULQI_PUBLIC_KEY=pk_live_xxxx
CULQI_SECRET_KEY=sk_live_xxxx

# OAuth — callback al dominio real
GOOGLE_REDIRECT_URI="https://anuncialo.pe/auth/google/callback"

# Admin: avisos de pago + permiso de devoluciones (SIN esto no llegan correos ni hay refunds)
ADMIN_EMAILS=tucorreo@gmail.com

# CORS: solo tu dominio
CORS_ALLOWED_ORIGINS="https://anuncialo.pe,https://www.anuncialo.pe"
```

> Después de editar el `.env` **siempre** corre:
> `php artisan config:clear && php artisan config:cache`
> (Laravel cachea la config; sin esto, tus cambios no se aplican.)

---

## Correo / SMTP (avisos de pago)

Con `MAIL_MAILER=log` los correos **no se envían** (solo se escriben en `storage/logs`).
Para recibir el aviso cuando se confirma un pago (a `ADMIN_EMAILS`):

1. En cPanel → **Cuentas de correo**, crea `noreply@anuncialo.pe`.
2. En el `.env` de producción:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.anuncialo.pe
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=noreply@anuncialo.pe
MAIL_PASSWORD=la_contraseña_del_buzon
MAIL_FROM_ADDRESS="noreply@anuncialo.pe"
MAIL_FROM_NAME="Anuncialo"
```

3. `php artisan config:cache`.

---

## Login Microsoft (OAuth)

Igual que Google, pero en [Azure Portal](https://portal.azure.com) → _Azure AD → Registros
de aplicaciones_. El Redirect URI debe ser `https://anuncialo.pe/auth/microsoft/callback`
y las variables `MICROSOFT_CLIENT_ID` / `MICROSOFT_CLIENT_SECRET` en el `.env`.

---

## Webhook de Culqi (confirmación asíncrona)

Para que las órdenes (PagoEfectivo/Cuotéalo) pasen solas a "pagado", registra el webhook
en **CulqiPanel → Desarrollo → Webhooks** apuntando a:

```
https://anuncialo.pe/culqi/webhook
```

Eventos: `charge.creation.succeeded` y `order.status.succeeded`.
El endpoint ya valida idempotencia y re-consulta el recurso a Culqi (anti-spoofing).

---

## Tareas programadas (cron del scheduler)

La **purga automática de la Papelera** (borra los anuncios con +30 días eliminados)
corre vía el scheduler de Laravel. Para que se ejecute, hace falta **un único cron**
en cPanel → **Cron Jobs** que dispare el scheduler **cada minuto**.

### En el formulario de cPanel → Cron Jobs

1. **Configuración común:** elige **"Una vez por minuto"** (rellena los 5 campos con `*`).
   O ponlos a mano: Minuto `*` · Hora `*` · Día `*` · Mes `*` · Día de la semana `*`.
2. **Comando** (ruta real de esta cuenta — usuario `anuncial`):

```
/usr/local/bin/php /home/anuncial/public_html/artisan schedule:run >> /dev/null 2>&1
```

3. Clic en **"Añadir nuevo trabajo de cron"**. Debe aparecer en _Trabajos de cron actuales_.

> Ese cron maneja **todas** las tareas programadas (`routes/console.php`), no solo la purga.
> La ruta del PHP (`/usr/local/bin/php`) la indica la propia página de Cron Jobs en
> _"PHP command examples"_; si cambia, usa la que muestre ahí.
> Para depurar, reemplaza `>> /dev/null 2>&1` por
> `>> /home/anuncial/public_html/storage/logs/cron.log 2>&1` y revisa ese archivo.

### Comandos de mantenimiento (papelera de anuncios)

```bash
php artisan ads:trash                  # lista los anuncios eliminados (papelera)
php artisan ads:trash --user=x@y.com   # solo los de un usuario
php artisan ads:trash --restore=ID     # restaura uno
php artisan ads:trash --purge=ID       # lo borra definitivamente
php artisan ads:purge-trash            # borra todos los de +30 días (lo hace el cron)
```

### Poblar anuncios de demostración

```bash
php artisan db:seed --class=AdsSeeder  # crea anuncios de prueba (cantidad en el seeder)
```

---

## Seguridad en producción

El código ya trae CORS restringido, rate limiting, CSRF, escape XSS, validación,
cabeceras de seguridad (middleware `SecurityHeaders`) y throttle en el webhook.
Faltan **2 ajustes que dependen del servidor**:

### 1. Cookie de sesión segura (en el `.env` de producción)

```env
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

Luego `php artisan config:cache`. Así la cookie de sesión nunca viaja por HTTP.

### 2. Forzar HTTPS (redirección + HSTS) en `.htaccess`

En `~/public_html/.htaccess`, dentro de `<IfModule mod_rewrite.c>`, antes de la regla
que redirige a `public/`:

```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
```

> La cabecera **HSTS** ya la envía la app (middleware `SecurityHeaders`) cuando la
> conexión es HTTPS, así que con la redirección de arriba queda completo.

### 3. Rotar secretos expuestos

Si alguna credencial se compartió (chat, captura, etc.), rótala:

- **Google Client Secret** → Google Cloud Console → Credenciales → regenerar.
- **Contraseña de BD** → cPanel → MySQL Databases → Change Password (y actualizar `.env`).
