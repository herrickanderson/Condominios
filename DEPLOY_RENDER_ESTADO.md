# Estado del Deploy - Condominios Backend

> **Última actualización**: 12 de Enero 2026
> **Estado**: ⚠️ En progreso - Error 500 pendiente de resolver

---

## ✅ Lo que ya está completado

### 1. Base de Datos en Neon.tech ✅
- **Host**: `ep-billowing-art-ahub324m-pooler.c-3.us-east-1.aws.neon.tech`
- **Database**: `neondb`
- **Username**: `neondb_owner`
- **Password**: `npg_w9TDW2uSyGaJ`
- **Puerto**: `5432`

### 2. Repositorio en GitHub ✅
- **URL**: https://github.com/herrickanderson/Condominios
- **Rama**: `main`
- **Cuenta GitHub**: `herrickanderson`

### 3. Deploy en Render.com ✅
- **URL de la API**: https://condominios-api.onrender.com
- **Build exitoso**: Nginx + PHP-FPM corriendo
- **Estado actual**: Error 500 (pendiente configurar)

---

## ⚠️ PENDIENTE - Resolver Error 500

El error 500 ocurre porque faltan ejecutar las migraciones y configurar el cache.

### Pasos para resolver:

1. **Ir a Render.com** → Tu servicio `condominios-api`
2. **Settings** → Buscar **"Pre-Deploy Command"**
3. **Agregar este comando**:
   ```
   php artisan config:cache && php artisan migrate --force
   ```
4. **Save Changes**
5. **Manual Deploy** → **Deploy latest commit**

### Variables de entorno que deben estar en Render:

| Variable | Valor |
|----------|-------|
| `APP_NAME` | `Condominios` |
| `APP_ENV` | `production` |
| `APP_KEY` | `base64:XDIFP1i5zWSfz/aqVGeEVtRYSihEBUFol1yiUya/IYo=` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://condominios-api.onrender.com` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `ep-billowing-art-ahub324m-pooler.c-3.us-east-1.aws.neon.tech` |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `neondb` |
| `DB_USERNAME` | `neondb_owner` |
| `DB_PASSWORD` | `npg_w9TDW2uSyGaJ` |
| `LOG_CHANNEL` | `errorlog` |

---

## 📁 Archivos creados durante el proceso

| Archivo | Descripción |
|---------|-------------|
| `Dockerfile` | Configuración Docker para Render |
| `docker/nginx.conf` | Configuración de Nginx |
| `docker/supervisord.conf` | Configuración de Supervisor |
| `env.production.example` | Template de variables para producción |
| `.env.copy` | Copia de respaldo de .env local |

---

## 🔄 Git - Configuración actual

```powershell
# Remote configurado
origin  https://github.com/herrickanderson/Condominios.git

# Para subir cambios
git add .
git commit -m "mensaje"
git push origin main
```

---

## 📌 Credenciales importantes

### Neon.tech (Base de datos)
- String de conexión completa:
```
postgresql://neondb_owner:npg_w9TDW2uSyGaJ@ep-billowing-art-ahub324m-pooler.c-3.us-east-1.aws.neon.tech/neondb?sslmode=require
```

### Render.com
- Panel: https://dashboard.render.com
- Servicio: `condominios-api`

---

## 🎯 Próximos pasos cuando retomes

1. ✅ Verificar que las variables de entorno estén en Render
2. ✅ Agregar el Pre-Deploy Command (migraciones)
3. ✅ Hacer un nuevo deploy manual
4. ✅ Verificar que la API responda sin error 500
5. ⬜ Probar los endpoints de la API

---

## 💡 Recordatorio - Trabajo local

Para trabajar localmente, usa tu archivo `.env` con esta configuración de BD:
```
DB_HOST=127.0.0.1
DB_DATABASE=bdsolufacil
DB_USERNAME=postgres
DB_PASSWORD=Adhar@201811
```

Para producción (Render), las variables están configuradas en el panel de Render.
