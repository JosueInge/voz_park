-- VozPark - Google Auth schema (PostgreSQL)
-- Ejecutar en la base de datos vozpark
-- Nota: no se usa BEGIN/COMMIT para evitar que un error deje el resto en estado 25P02.

-- 0) Crea usuarios si aun no existe (para evitar error 42P01)
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) DEFAULT NULL,
    zona VARCHAR(120) DEFAULT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    institucion VARCHAR(120) DEFAULT 'Alcaldía SS',
    rol VARCHAR(20) NOT NULL DEFAULT 'poblador'
        CHECK (rol IN ('poblador', 'admin')),
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

-- 1) Extiende tabla usuarios para login con Google
ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS google_sub VARCHAR(64),
    ADD COLUMN IF NOT EXISTS auth_provider VARCHAR(20) NOT NULL DEFAULT 'local',
    ADD COLUMN IF NOT EXISTS email_verificado BOOLEAN NOT NULL DEFAULT FALSE,
    ADD COLUMN IF NOT EXISTS ultimo_login TIMESTAMP NULL;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint c
        JOIN pg_class t ON t.oid = c.conrelid
        WHERE c.conname = 'usuarios_auth_provider_check'
          AND t.relname = 'usuarios'
    ) THEN
        ALTER TABLE usuarios
            ADD CONSTRAINT usuarios_auth_provider_check
            CHECK (auth_provider IN ('local', 'google'));
    END IF;
END $$;

CREATE UNIQUE INDEX IF NOT EXISTS uq_usuarios_google_sub
    ON usuarios (google_sub)
    WHERE google_sub IS NOT NULL;

CREATE INDEX IF NOT EXISTS idx_usuarios_auth_provider
    ON usuarios (auth_provider);

-- 2) Tabla de sesiones para token propio emitido por backend
CREATE TABLE IF NOT EXISTS sesiones_usuario (
    id BIGSERIAL PRIMARY KEY,
    usuario_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    token_hash CHAR(64) NOT NULL UNIQUE,
    provider VARCHAR(20) NOT NULL DEFAULT 'local',
    ip VARCHAR(64) NULL,
    user_agent VARCHAR(255) NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT NOW(),
    expira_en TIMESTAMP NOT NULL,
    revocado BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE INDEX IF NOT EXISTS idx_sesiones_usuario_usuario
    ON sesiones_usuario (usuario_id);

CREATE INDEX IF NOT EXISTS idx_sesiones_usuario_expira
    ON sesiones_usuario (expira_en);
