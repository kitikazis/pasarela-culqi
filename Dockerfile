# 1. Usar la imagen oficial de PHP 8.2
FROM php:8.2-cli

# 2. Instalar herramientas necesarias del sistema y extensiones de base de datos
RUN apt-get update && apt-get install -y git unzip curl libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# 3. Instalar Node.js (necesario para que tu Vite compile el frontend)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 4. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Movernos a la carpeta de trabajo
WORKDIR /app

# 6. Copiar todos los archivos de tu proyecto al servidor
COPY . .

# 7. Compilar tu frontend con Vite
RUN npm install && npm run build

# 8. Instalar dependencias de PHP
RUN composer install --no-dev

# 9. Iniciar el servidor apuntando a tu carpeta public usando el puerto de Render
CMD php -S 0.0.0.0:$PORT -t public