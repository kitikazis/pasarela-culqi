#!/usr/bin/env bash
#
# deploy.sh — Setup/actualizacion del proyecto en cPanel (produccion).
#
# Uso:
#   1) git clone https://github.com/kitikazis/pasarela-culqi.git .   (dentro de ~/public_html)
#   2) bash deploy.sh
#      -> Primera vez: crea el .env y se DETIENE para que pongas las credenciales.
#         Edita el .env (nano .env) y vuelve a correr: bash deploy.sh
#   3) Dia a dia (ya configurado): git pull && bash deploy.sh
#
set -euo pipefail
cd "$(dirname "$0")"

# --- Detecta el ejecutable de composer -------------------------------------
if command -v composer >/dev/null 2>&1; then
    COMPOSER="composer"
elif [ -f composer.phar ]; then
    COMPOSER="php composer.phar"
else
    echo "ERROR: no encuentro 'composer' ni 'composer.phar'. Instalalo primero."
    exit 1
fi

# --- Fase 1: si no hay .env, lo creamos y paramos --------------------------
if [ ! -f .env ]; then
    echo ">> No existe .env. Lo creo desde .env.example..."
    cp .env.example .env
    $COMPOSER install --no-dev --optimize-autoloader
    php artisan key:generate
    echo
    echo "============================================================"
    echo " .env creado. AHORA edita las credenciales de produccion:"
    echo "   nano .env"
    echo
    echo " Cambia como minimo:"
    echo "   APP_ENV=production   APP_DEBUG=false"
    echo "   APP_URL=https://anuncialo.pe"
    echo "   DB_DATABASE / DB_USERNAME / DB_PASSWORD  (tu MySQL de cPanel)"
    echo "   CULQI_PUBLIC_KEY / CULQI_SECRET_KEY      (llaves pk_live_ / sk_live_)"
    echo "   CULQI_RSA_ID / CULQI_RSA_PUBLIC_KEY      (CulqiPanel -> RSA Keys)"
    echo "   GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET"
    echo
    echo " Cuando termines, vuelve a correr:  bash deploy.sh"
    echo "============================================================"
    exit 0
fi

# --- Fase 2: deploy completo -----------------------------------------------
echo ">> Instalando dependencias (sin dev)..."
$COMPOSER install --no-dev --optimize-autoloader

echo ">> Genera APP_KEY si falta..."
grep -q '^APP_KEY=base64:' .env || php artisan key:generate

echo ">> Crea el .htaccess redirector si no existe (manda todo a public/)..."
if [ ! -f .htaccess ]; then
    cat > .htaccess <<'HT'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
HT
fi

echo ">> Enlace simbolico de storage (si no existe)..."
php artisan storage:link || true

echo ">> Migraciones..."
php artisan migrate --force

echo ">> Limpiando y cacheando configuracion..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

echo ">> Permisos de storage y cache..."
chmod -R 775 storage bootstrap/cache

echo
echo ">> Listo. Recarga https://anuncialo.pe"
