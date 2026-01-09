# Recomendaciones de Hosting Gratuito para Laravel + PostgreSQL

Aquí detallo las mejores opciones para desplegar tu sistema (Backend Laravel + Base de Datos Postgres) de forma gratuita o de prueba.

## Opción 1: Render.com + Neon.tech (Recomendada 🌟)
Esta opción separa la BD del código para obtener lo mejor de ambos proveedores sin costo.

### 1. Backend (Laravel) en Render.com
- **Plan:** Free Tier (Web Services).
- **Ventajas:**
  - Despliegue automático conectando tu repositorio de GitHub.
  - Soporte nativo para Docker o entornos PHP.
  - Certificado SSL (HTTPS) gratuito automático.
- **Limitaciones:** El servidor entra en suspensión (sleep) si no recibe tráfico en 15 minutos (tarda unos segundos en despertar en la primera petición).
- **Base de datos:** Render ofrece BD de Postgres gratis, pero solo dura 90 días. Por eso usamos Neon.

### 2. Base de Datos en Neon.tech
- **Plan:** Free Tier.
- **Ventajas:**
  - Especializado en PostgreSQL Serverless.
  - **No expira** a los 90 días como Render.
  - Capa gratuita generosa (0.5 GB almacenamiento).
- **Integración:** Creas la BD en Neon, copias la string de conexión (`postgres://...`) y la configuras en las Variables de Entorno de Render.

---

## Opción 2: AWS Free Tier (12 Meses)
Ideal si quieres usar la infraestructura de Amazon que ya tenías configurada en tu `.env`.

- **Duración:** 12 meses gratis para cuentas nuevas.
- **Servicios:**
  - **EC2 (t2.micro / t3.micro):** Máquina virtual para instalar Linux, Nginx/Apache y PHP. Requiere configuración manual del servidor.
  - **RDS:** Base de datos gestionada (750 horas/mes) para PostgreSQL.
- **Ventajas:** Infraestructura empresarial, no se "duerme".
- **Requisitos:** Tarjeta de crédito para el registro.

---

## Opción 3: Oracle Cloud "Always Free"
- **Duración:** De por vida (según disponibilidad).
- **Recursos:** 2 Máquinas virtuales (VM.Standard.E2.1.Micro) y Base de Datos Autónoma.
- **Ventajas:** Recursos muy superiores en RAM y CPU comparado a AWS o Google gratis.
- **Contras:** El proceso de registro es estricto y a veces rechazan tarjetas sin explicación.

## Resumen de Pasos para Render + Neon
1. Subir código a **GitHub**.
2. Crear BD en **Neon.tech** y obtener credenciales.
3. Crear Web Service en **Render.com** conectado a GitHub.
4. Configurar variables de entorno en Render (`DB_HOST`, `DB_PASSWORD`, etc.) con los datos de Neon.
