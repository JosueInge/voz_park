-- ============================================================
--  VozPark — Schema PostgreSQL
--  Compatible con pgAdmin 4 / PostgreSQL 13+
--  Ejecutar como superusuario o el dueño de la base de datos
-- ============================================================

-- Crear la base de datos (ejecutar conectado a postgres)
-- CREATE DATABASE vozpark WITH ENCODING 'UTF8' LC_COLLATE 'es_SV.UTF-8';
-- \c vozpark

-- ────────────────────────────────────────────────────────────
-- EXTENSIONES
-- ────────────────────────────────────────────────────────────
CREATE EXTENSION IF NOT EXISTS "pgcrypto";   -- para gen_random_uuid() si se necesita


-- ════════════════════════════════════════════════════════════
-- 1. USUARIOS
-- ════════════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS usuarios (
    id           SERIAL PRIMARY KEY,
    nombre       VARCHAR(120)  NOT NULL,
    correo       VARCHAR(180)  NOT NULL UNIQUE,
    password     VARCHAR(255)  NOT NULL,
    telefono     VARCHAR(20)   DEFAULT NULL,
    zona         VARCHAR(120)  DEFAULT NULL,
    foto_perfil  VARCHAR(255)  DEFAULT NULL,
    institucion  VARCHAR(120)  DEFAULT 'Alcaldía SS',
    rol          VARCHAR(20)   NOT NULL DEFAULT 'poblador'
                               CHECK (rol IN ('poblador','admin')),
    created_at   TIMESTAMP     NOT NULL DEFAULT NOW(),
    updated_at   TIMESTAMP     NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE  usuarios           IS 'Ciudadanos y administradores del sistema';
COMMENT ON COLUMN usuarios.rol       IS 'poblador | admin';
COMMENT ON COLUMN usuarios.password  IS 'Hash bcrypt';

-- Índice para login por correo
CREATE INDEX IF NOT EXISTS idx_usuarios_correo ON usuarios(correo);


-- ════════════════════════════════════════════════════════════
-- 2. PARQUES
-- ════════════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS parques (
    id         SERIAL PRIMARY KEY,
    nombre     VARCHAR(120) NOT NULL,
    latitud    NUMERIC(10,7) NOT NULL
                            CHECK (latitud  BETWEEN -90  AND 90),
    longitud   NUMERIC(10,7) NOT NULL
                            CHECK (longitud BETWEEN -180 AND 180),
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE parques IS 'Parques urbanos de San Salvador con coordenadas GPS';

CREATE INDEX IF NOT EXISTS idx_parques_nombre ON parques(nombre);

-- Datos semilla: 25 parques de San Salvador
INSERT INTO parques (nombre, latitud, longitud) VALUES
('Plaza Libertad',                  13.6966, -89.1904),
('Parque Bolívar',                  13.6970, -89.1920),
('Parque Morazán',                  13.6982, -89.1910),
('Parque San José',                 13.6940, -89.1930),
('Parque Cuscatlán',                13.6930, -89.2183),
('Parque Infantil de Diversiones',  13.7010, -89.2100),
('Plaza Gerardo Barrios',           13.6960, -89.1895),
('Parque Centenario',               13.6945, -89.1925),
('Parque Simón Bolívar',            13.6950, -89.2250),
('Parque España',                   13.6975, -89.2020),
('Parque San Jacinto',              13.6820, -89.2010),
('Parque Colonia Médica',           13.7005, -89.2180),
('Parque La Concordia',             13.6935, -89.1940),
('Parque San Miguelito',            13.7080, -89.2220),
('Parque Universitario (UES)',      13.7230, -89.2090),
('Parque Colonia Escalón',          13.7005, -89.2310),
('Parque Colonia Maya',             13.6990, -89.2290),
('Parque Colonia Layco',            13.6950, -89.2270),
('Parque Colonia Flor Blanca',      13.7015, -89.2350),
('Parque Colonia Miramonte',        13.7020, -89.2230),
('Parque Colonia Atlacatl',         13.7040, -89.2180),
('Parque Colonia Zacamil',          13.7160, -89.2200),
('Parque Colonia Centroamericana',  13.7060, -89.2100),
('Parque Colonia San Luis',         13.7000, -89.2260),
('Parque Colonia San Antonio Abad', 13.6880, -89.2020)
ON CONFLICT DO NOTHING;


-- ════════════════════════════════════════════════════════════
-- 3. PERSONAL MUNICIPAL
-- ════════════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS personal_municipal (
    id               SERIAL PRIMARY KEY,
    nombre           VARCHAR(120) NOT NULL,
    especialidad     VARCHAR(80)  DEFAULT NULL,
    estado           VARCHAR(20)  NOT NULL DEFAULT 'activo'
                                  CHECK (estado IN ('activo','inactivo')),
    latitud_actual   NUMERIC(10,7) DEFAULT NULL
                                   CHECK (latitud_actual  IS NULL
                                       OR latitud_actual  BETWEEN -90  AND 90),
    longitud_actual  NUMERIC(10,7) DEFAULT NULL
                                   CHECK (longitud_actual IS NULL
                                       OR longitud_actual BETWEEN -180 AND 180),
    created_at       TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at       TIMESTAMP NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE personal_municipal IS 'Personal municipal que atiende incidencias en campo';

CREATE INDEX IF NOT EXISTS idx_personal_estado ON personal_municipal(estado);

-- Datos semilla
INSERT INTO personal_municipal (nombre, especialidad, estado, latitud_actual, longitud_actual) VALUES
('Luis Martínez', 'Electricista',        'activo',   13.6992, -89.1915),
('Ana García',    'Servicios Generales', 'activo',   13.7068, -89.2055),
('Pedro López',   'Jardinería',          'activo',   13.6870, -89.2300),
('María Rivas',   'Mantenimiento',       'activo',   13.6938, -89.2195),
('Carlos Vega',   'Plomería',            'activo',   13.6950, -89.2230),
('Jorge Flores',  'Seguridad',           'inactivo', NULL,    NULL)
ON CONFLICT DO NOTHING;


-- ════════════════════════════════════════════════════════════
-- 4. REPORTES (incidencias ciudadanas)
-- ════════════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS reportes (
    id                 SERIAL PRIMARY KEY,
    usuario_id         INTEGER      NOT NULL
                                    REFERENCES usuarios(id) ON DELETE CASCADE,
    tipo_incidencia    VARCHAR(80)  NOT NULL,
    parque_id          INTEGER      NOT NULL
                                    REFERENCES parques(id),
    urgencia           VARCHAR(10)  NOT NULL
                                    CHECK (urgencia IN ('Alta','Media','Baja')),
    descripcion        TEXT         NOT NULL,
    ubicacion_parque   VARCHAR(255) NOT NULL,
    nombre_reportante  VARCHAR(120) NOT NULL,
    estado             VARCHAR(20)  NOT NULL DEFAULT 'Sin asignar'
                                    CHECK (estado IN (
                                        'Sin asignar','Asignado',
                                        'En curso','En revisión','Resuelto'
                                    )),
    personal_id        INTEGER      DEFAULT NULL
                                    REFERENCES personal_municipal(id)
                                    ON DELETE SET NULL,
    fecha_reporte      TIMESTAMP    NOT NULL DEFAULT NOW(),
    updated_at         TIMESTAMP    NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE  reportes              IS 'Incidencias reportadas por ciudadanos';
COMMENT ON COLUMN reportes.urgencia     IS 'Alta | Media | Baja';
COMMENT ON COLUMN reportes.estado       IS 'Sin asignar | Asignado | En curso | En revisión | Resuelto';
COMMENT ON COLUMN reportes.personal_id  IS 'Personal asignado para atender la incidencia';

CREATE INDEX IF NOT EXISTS idx_reportes_usuario   ON reportes(usuario_id);
CREATE INDEX IF NOT EXISTS idx_reportes_parque    ON reportes(parque_id);
CREATE INDEX IF NOT EXISTS idx_reportes_estado    ON reportes(estado);
CREATE INDEX IF NOT EXISTS idx_reportes_personal  ON reportes(personal_id);
CREATE INDEX IF NOT EXISTS idx_reportes_fecha     ON reportes(fecha_reporte DESC);


-- ════════════════════════════════════════════════════════════
-- 5. IMÁGENES DE REPORTES
-- ════════════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS reporte_imagenes (
    id          SERIAL  PRIMARY KEY,
    reporte_id  INTEGER NOT NULL
                        REFERENCES reportes(id) ON DELETE CASCADE,
    ruta        VARCHAR(255) NOT NULL
);

COMMENT ON TABLE reporte_imagenes IS 'Rutas de imágenes adjuntas a cada reporte';

CREATE INDEX IF NOT EXISTS idx_img_reporte ON reporte_imagenes(reporte_id);


-- ════════════════════════════════════════════════════════════
-- 6. HISTORIAL DE SEGUIMIENTO DE INCIDENCIAS
-- ════════════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS incidencia_historial (
    id              SERIAL  PRIMARY KEY,
    reporte_id      INTEGER NOT NULL
                            REFERENCES reportes(id) ON DELETE CASCADE,
    tipo_evento     VARCHAR(60)  NOT NULL,
    descripcion     TEXT         DEFAULT NULL,
    nombre_personal VARCHAR(120) DEFAULT NULL,
    fecha_evento    TIMESTAMP    NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE  incidencia_historial             IS 'Eventos de seguimiento por incidencia';
COMMENT ON COLUMN incidencia_historial.tipo_evento IS 'Reporte recibido | Asignado a | Resuelto';

CREATE INDEX IF NOT EXISTS idx_historial_reporte ON incidencia_historial(reporte_id);
CREATE INDEX IF NOT EXISTS idx_historial_fecha   ON incidencia_historial(fecha_evento);


-- ════════════════════════════════════════════════════════════
-- 7. NOTIFICACIONES
-- ════════════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS notificaciones (
    id          SERIAL  PRIMARY KEY,
    usuario_id  INTEGER NOT NULL
                        REFERENCES usuarios(id) ON DELETE CASCADE,
    categoria   VARCHAR(20) NOT NULL
                            CHECK (categoria IN ('Reportes','Alertas')),
    mensaje     TEXT        NOT NULL,
    enlace      VARCHAR(255) DEFAULT NULL,
    fecha_hora  TIMESTAMP   NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE  notificaciones           IS 'Notificaciones automáticas para ciudadanos';
COMMENT ON COLUMN notificaciones.categoria IS 'Reportes | Alertas';

CREATE INDEX IF NOT EXISTS idx_notif_usuario ON notificaciones(usuario_id);
CREATE INDEX IF NOT EXISTS idx_notif_fecha   ON notificaciones(fecha_hora DESC);


-- ════════════════════════════════════════════════════════════
-- 8. ENCUESTAS
-- ════════════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS encuestas (
    id           SERIAL  PRIMARY KEY,
    titulo       VARCHAR(200) NOT NULL,
    descripcion  TEXT         DEFAULT NULL,
    fecha_inicio DATE         NOT NULL,
    fecha_cierre DATE         NOT NULL,
    activa       BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at   TIMESTAMP    NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_fechas_encuesta CHECK (fecha_cierre >= fecha_inicio)
);

COMMENT ON TABLE encuestas IS 'Encuestas ciudadanas; las próximas a vencer generan notificaciones de Alertas';


-- ════════════════════════════════════════════════════════════
-- FUNCIÓN: updated_at automático
-- Aplica a: usuarios, reportes, personal_municipal
-- ════════════════════════════════════════════════════════════
CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$;

CREATE OR REPLACE TRIGGER trg_usuarios_updated
    BEFORE UPDATE ON usuarios
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE OR REPLACE TRIGGER trg_reportes_updated
    BEFORE UPDATE ON reportes
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE OR REPLACE TRIGGER trg_personal_updated
    BEFORE UPDATE ON personal_municipal
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();


-- ════════════════════════════════════════════════════════════
-- FUNCIÓN + TRIGGER: registrar evento en historial
-- al INSERT de un reporte → inserta evento "Reporte recibido"
-- ════════════════════════════════════════════════════════════
CREATE OR REPLACE FUNCTION registrar_reporte_recibido()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    INSERT INTO incidencia_historial (reporte_id, tipo_evento, descripcion)
    VALUES (NEW.id, 'Reporte recibido', 'Reporte registrado en el sistema.');
    RETURN NEW;
END;
$$;

CREATE OR REPLACE TRIGGER trg_reporte_recibido
    AFTER INSERT ON reportes
    FOR EACH ROW EXECUTE FUNCTION registrar_reporte_recibido();


-- ════════════════════════════════════════════════════════════
-- FUNCIÓN + TRIGGER: notificación automática al cambiar estado
-- cuando updated_at cambia en reportes → inserta notificación
-- ════════════════════════════════════════════════════════════
CREATE OR REPLACE FUNCTION notificar_cambio_estado()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    IF OLD.estado IS DISTINCT FROM NEW.estado THEN
        INSERT INTO notificaciones (usuario_id, categoria, mensaje, enlace)
        VALUES (
            NEW.usuario_id,
            'Reportes',
            'Tu reporte #' || NEW.id || ' cambió de estado a: ' || NEW.estado || '.',
            '/reportes/' || NEW.id
        );
    END IF;
    RETURN NEW;
END;
$$;

CREATE OR REPLACE TRIGGER trg_notificar_estado
    AFTER UPDATE ON reportes
    FOR EACH ROW EXECUTE FUNCTION notificar_cambio_estado();


-- ════════════════════════════════════════════════════════════
-- VISTA: estado general de parques
-- Usada por GET /parques y GET /admin/parques
-- ════════════════════════════════════════════════════════════
CREATE OR REPLACE VIEW vista_parques_estado AS
SELECT
    p.id,
    p.nombre,
    p.latitud,
    p.longitud,
    CASE
        WHEN COUNT(r.id) FILTER (
            WHERE r.urgencia = 'Alta' AND r.estado != 'Resuelto'
        ) > 0 THEN 'Urgente'
        WHEN COUNT(r.id) FILTER (
            WHERE r.estado IN ('En curso','En revisión')
        ) > 0 THEN 'En revisión'
        ELSE 'Activo'
    END AS estado_general
FROM parques p
LEFT JOIN reportes r ON r.parque_id = p.id
GROUP BY p.id, p.nombre, p.latitud, p.longitud;

COMMENT ON VIEW vista_parques_estado IS
    'Estado calculado de cada parque según sus incidencias activas';


-- ════════════════════════════════════════════════════════════
-- VISTA: métricas del panel admin
-- ════════════════════════════════════════════════════════════
CREATE OR REPLACE VIEW vista_metricas_admin AS
SELECT
    (SELECT COUNT(*) FROM reportes
      WHERE estado != 'Resuelto')                              AS reportes_activos,
    (SELECT COUNT(*) FROM reportes
      WHERE urgencia = 'Alta' AND estado != 'Resuelto')       AS reportes_urgentes,
    (SELECT COUNT(*) FROM reportes
      WHERE estado = 'Resuelto'
        AND updated_at::date = CURRENT_DATE)                  AS resueltos_hoy,
    (SELECT COUNT(*) FROM personal_municipal
      WHERE estado = 'activo')                                 AS personal_activo,
    (SELECT COUNT(DISTINCT parque_id) FROM reportes
      WHERE estado != 'Resuelto')                             AS parques_cubiertos,
    (SELECT COUNT(*) FROM parques)                             AS parques_total;

COMMENT ON VIEW vista_metricas_admin IS
    'Métricas consolidadas para las tarjetas del panel principal admin';


-- ════════════════════════════════════════════════════════════
-- VERIFICACIÓN FINAL
-- ════════════════════════════════════════════════════════════
DO $$
DECLARE
    tablas TEXT[] := ARRAY[
        'usuarios','parques','personal_municipal','reportes',
        'reporte_imagenes','incidencia_historial','notificaciones','encuestas'
    ];
    t TEXT;
BEGIN
    FOREACH t IN ARRAY tablas LOOP
        IF NOT EXISTS (
            SELECT 1 FROM information_schema.tables
             WHERE table_schema = 'public' AND table_name = t
        ) THEN
            RAISE EXCEPTION 'Tabla % no fue creada correctamente', t;
        ELSE
            RAISE NOTICE 'OK: tabla % lista', t;
        END IF;
    END LOOP;
    RAISE NOTICE '=== Schema VozPark creado correctamente ===';
END;
$$;