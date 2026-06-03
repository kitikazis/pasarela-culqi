# Despliegue en cPanel (enlix.pe)

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
- **SSH** — es solo la *puerta* para entrar al servidor y dar las órdenes (`git pull`, etc.).
  No "conecta" nada por sí mismo; es tu acceso a la línea de comandos del servidor.
- **`git remote`** — lo que hace que `~/public_html` sepa *de dónde* bajar los cambios.

---

## Particularidades del servidor

### Document Root → carpeta `public/`
El dominio principal `enlix.pe` apunta a `/public_html` (no se puede cambiar en el
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
- **`.env`** — configuración de producción (credenciales, `APP_URL=https://enlix.pe`, etc.).
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

## Día a día: subir cambios a producción

**En tu PC** (editas y subes a GitHub):

```bash
git add .
git commit -m "lo que cambié"
git push origin main
```

**En el servidor** (por SSH, bajas los cambios):

```bash
cd ~/public_html && git pull origin main && php artisan optimize:clear
```

Listo. Recarga `enlix.pe` y verás los cambios.

---

## Login social (Google OAuth)

El `redirect_uri` debe estar autorizado en
[Google Cloud Console](https://console.cloud.google.com) → *APIs y servicios → Credenciales*.
Cada entorno usa su propia URL (debe coincidir **exacta**: sin barra final, con/sin `https`):

| Entorno | Redirect URI autorizado | JS Origin |
|---------|-------------------------|-----------|
| Producción | `https://enlix.pe/auth/google/callback` | `https://enlix.pe` |
| Local | `http://localhost:8000/auth/google/callback` | `http://localhost:8000` |

> El valor lo toma de `GOOGLE_REDIRECT_URI` en el `.env` de cada entorno.
> Tras editar en Google Cloud, puede tardar de 5 min a 1 h en aplicarse.

---

## Montos mínimos de Culqi (métodos de pago)

Culqi **oculta** métodos cuyo mínimo no alcanza el monto del plan:

| Monto del plan | Métodos visibles |
|----------------|------------------|
| S/ 1.00 – 5.99 | Solo 💳 Tarjeta |
| Desde S/ 6.00  | Tarjeta + 📱 Yape + 👛 Billeteras |

> Para que aparezcan **todos** los métodos en producción, usa **S/ 6.00 o más**
> en [`config/plans.php`](config/plans.php). S/ 1.00 sirve solo para probar con tarjeta.
