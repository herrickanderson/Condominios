# --- Stage 1: Build Vendor (Composer) ---
FROM composer:2 as vendor
WORKDIR /app
COPY composer.json composer.lock ./
# Instalar dependencias para tener la carpeta vendor (necesaria para Ziggy)
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --ignore-platform-reqs \
    --optimize-autoloader \
    --no-scripts

# --- Stage 2: Build Frontend Assets (Node.js) ---
FROM node:20 as frontend
WORKDIR /app
COPY package*.json vite.config.js ./
RUN npm install
COPY resources ./resources
COPY public ./public
# Truco: Copiar la carpeta vendor de Ziggy para que Vite la encuentre
COPY --from=vendor /app/vendor/tightenco/ziggy ./vendor/tightenco/ziggy
# Construir los assets
RUN npm run build

# --- Stage 3: Build Backend (PHP) ---
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Copiar carpetas generadas de los stages anteriores
COPY --from=vendor /app/vendor /var/www/html/vendor
COPY --from=frontend /app/public/build /var/www/html/public/build

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copiar configuraciones
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000

CMD ["/usr/local/bin/docker-entrypoint.sh"]
