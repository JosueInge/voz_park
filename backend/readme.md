# VozPark — Backend API

PHP puro · MySQL · JWT · Sin frameworks externos

---

## Estructura de directorios

```
backend/
├── .env                      Variables de entorno (no subir a git)
├── .htaccess                 Reescritura de rutas Apache
├── index.php                 Router principal
├── database.sql              Schema + datos semilla de parques
│
├── config/
│   └── database.php          Conexión PDO singleton + carga de .env
│
├── helpers/
│   ├── jwt.php               Generación y validación de JWT (HS256)
│   └── response.php          Helpers jsonResponse / success / error
│
├── middleware/
│   └── auth.php              requireAuth($rol) — valida token y rol
│
├── models/
│   ├── Usuario.php           CRUD de usuarios y estadísticas
│   ├── Reporte.php           CRUD de reportes e imágenes
│   ├── Parque.php            Listado y búsqueda de parques
│   └── Notificacion.php      CRUD de notificaciones
│
├── controllers/
│   ├── EncabezadoController.php   Bienvenida + búsqueda global
│   ├── ReporteController.php      Mis reportes, detalle, crear, recientes
│   ├── PerfilController.php       Ver perfil + actualizar foto
│   ├── ParqueController.php       Listar parques + API Key de Maps
│   └── NotificacionController.php Listar + eliminar notificaciones
│
└── uploads/
    ├── reportes/             Imágenes de reportes ciudadanos
    └── perfiles/             Fotos de perfil (creada automáticamente)
```

---

## Configuración inicial

1. Copiar `.env` y completar los valores reales:
   ```
   DB_HOST, DB_NAME, DB_USER, DB_PASS
   JWT_SECRET   (cadena larga aleatoria)
   GOOGLE_MAPS_API_KEY
   ```

2. Importar la base de datos:
   ```bash
   mysql -u root -p < database.sql
   ```

3. Ajustar `RewriteBase` en `.htaccess` si la ruta del proyecto es diferente.

4. Dar permisos de escritura a la carpeta `uploads/`:
   ```bash
   chmod -R 755 uploads/
   ```

---

## Endpoints disponibles

Todos los endpoints (salvo login/registro) requieren:
```
Authorization: Bearer <token>
```

### Encabezado

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/bienvenida` | Retorna el nombre del usuario autenticado |
| GET | `/api/buscar?q=texto` | Búsqueda global de parques y usuarios |

### Reportes

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/reportes/mis-reportes` | Lista los reportes del usuario autenticado |
| GET | `/reportes/{id}` | Detalle de un reporte (solo del usuario) |
| POST | `/reportes` | Crea un nuevo reporte con imágenes |
| GET | `/incidencias/recientes` | Incidencias recientes para el mapa |

#### POST `/reportes` — campos requeridos (multipart/form-data)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `tipo_incidencia` | string | Iluminación, Vegetación, etc. |
| `parque_id` | int | ID del parque |
| `urgencia` | string | Alta / Media / Baja |
| `descripcion` | string | Descripción del problema |
| `ubicacion_parque` | string | Ubicación dentro del parque |
| `nombre_reportante` | string | Nombre (opcional, se toma del token) |
| `imagenes[]` | file | 1–2 imágenes JPG/PNG, máx. 2MB c/u |

### Perfil

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/perfil` | Información completa + estadísticas |
| POST | `/perfil/foto` | Actualiza foto de perfil (multipart) |

### Parques

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/parques` | Todos los parques con coordenadas y estado |
| GET | `/parques?estado=Urgente` | Filtrar por estado |
| GET | `/config/maps` | Retorna la API Key de Google Maps |

### Notificaciones

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/notificaciones` | Todas las notificaciones del usuario |
| GET | `/notificaciones?categoria=Reportes` | Filtradas por categoría |
| DELETE | `/notificaciones/{id}` | Descarta una notificación |

---

## Formato de respuestas

**Éxito:**
```json
{
  "success": true,
  "message": "Mensaje descriptivo",
  "data": { ... }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Descripción del error"
}
```

**Error de validación (422):**
```json
{
  "success": false,
  "message": "Error al enviar el reporte",
  "errores": ["El tipo de incidencia es obligatorio", "..."]
}
```

---

## Generación automática de notificaciones

Las notificaciones se crean llamando a los métodos estáticos del modelo:

```php
// Al cambiar el estado de un reporte (desde el panel admin):
Notificacion::crearCambioEstado($usuarioId, $reporteId, $nuevoEstado);

// Al detectar una encuesta próxima a vencer (cron job):
Notificacion::crearAlertaEncuesta($usuarioId, $encuestaId, $titulo);
```

---

## Endpoints Admin (home_admin)

Todos requieren `Authorization: Bearer <token>` con rol `admin`.

### Encabezado admin

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/admin/perfil` | Nombre, rol e institución del admin autenticado |

### Métricas del panel

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/admin/metricas/reportes-activos` | Total activos + cuántos son urgencia Alta |
| GET | `/admin/metricas/resueltos-hoy` | Reportes resueltos hoy |
| GET | `/admin/metricas/personal-activo` | Personal con estado activo |
| GET | `/admin/metricas/parques-cubiertos` | Parques con reporte activo vs total |

### Reportes pendientes

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/admin/reportes/pendientes` | 5 más recientes con estado Sin asignar o Asignado |
| GET | `/admin/reportes/pendientes?todos=1` | Todos los pendientes |
| GET | `/admin/reportes/exportar-pdf` | Genera PDF descargable (requiere FPDF en `/backend/lib/fpdf/`) |

### Gestión de incidencias

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/admin/incidencias` | Todas las incidencias |
| GET | `/admin/incidencias?busqueda=X&urgencia=Alta&estado=En curso&parque=Y` | Búsqueda + filtros combinados |
| GET | `/admin/incidencias/{id}` | Detalle + imágenes + historial |
| PUT | `/admin/incidencias/{id}` | Actualiza estado/personal, registra evento |

#### PUT `/admin/incidencias/{id}` — body JSON

```json
{
  "estado":      "En curso",
  "personal_id": 3
}
```

### Mapa de incidencias

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/admin/parques` | Parques con coordenadas y estado |
| GET | `/admin/parques?estado=Urgente` | Filtrar por estado |
| GET | `/admin/incidencias/recientes` | Últimas incidencias para el panel del mapa |
| GET | `/admin/personal/campo` | Personal activo con coordenadas (marcadores) |
| GET | `/admin/personal/disponible` | Lista de personal para el selector de asignación |

---

## Instalación de FPDF (exportar PDF)

```bash
# Desde la raíz del backend
mkdir -p lib/fpdf
curl -L https://www.fpdf.org/dl.php?v=186 -o fpdf.zip
unzip fpdf.zip -d lib/fpdf/
# Verificar que exista: lib/fpdf/fpdf.php
```