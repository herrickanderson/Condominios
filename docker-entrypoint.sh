#!/bin/sh
set -e

# Imprimir lo que está pasando
echo "🚀 Iniciando proceso de despliegue..."

# Rutina de caché y optimización de Laravel
echo "🔥 Cacheando configuración..."
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# Ejecutar migraciones
echo "📦 Ejecutando migraciones de base de datos..."
php artisan migrate --force

# Crear link simbólico de storage si no existe
echo "🔗 Verificando storage link..."
php artisan storage:link || true

# Iniciar Supervisor (que a su vez inicia Nginx y PHP-FPM)
echo "✅ Iniciando servidor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
