-- ============================================================
-- DECAMERON API — Dump de Base de Datos PostgreSQL
-- Generado para prueba técnica
-- ============================================================

-- Crear base de datos (ejecutar como superusuario)
-- CREATE DATABASE decameron_db;

-- Conectarse a la base de datos antes de ejecutar el resto:
-- \c decameron_db

-- ─────────────────────────────────────────────
-- Tabla: room_types (catálogo de tipos)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS room_types (
    id         BIGSERIAL    PRIMARY KEY,
    name       VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP    NULL,
    updated_at TIMESTAMP    NULL
);

-- ─────────────────────────────────────────────
-- Tabla: accommodations (catálogo de acomodaciones)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS accommodations (
    id         BIGSERIAL    PRIMARY KEY,
    name       VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP    NULL,
    updated_at TIMESTAMP    NULL
);

-- ─────────────────────────────────────────────
-- Tabla: hotels
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS hotels (
    id          BIGSERIAL    PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    address     VARCHAR(200) NOT NULL,
    city        VARCHAR(100) NOT NULL,
    nit         VARCHAR(20)  NOT NULL UNIQUE,
    total_rooms INTEGER      NOT NULL CHECK (total_rooms >= 1),
    created_at  TIMESTAMP    NULL,
    updated_at  TIMESTAMP    NULL
);

-- ─────────────────────────────────────────────
-- Tabla: hotel_rooms (configuraciones de habitación)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS hotel_rooms (
    id               BIGSERIAL PRIMARY KEY,
    hotel_id         BIGINT    NOT NULL REFERENCES hotels(id) ON DELETE CASCADE,
    room_type_id     BIGINT    NOT NULL REFERENCES room_types(id) ON DELETE RESTRICT,
    accommodation_id BIGINT    NOT NULL REFERENCES accommodations(id) ON DELETE RESTRICT,
    quantity         INTEGER   NOT NULL CHECK (quantity >= 1),
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,

    -- Unicidad compuesta: no puede repetirse tipo+acomodación en el mismo hotel
    CONSTRAINT unique_hotel_room_config
        UNIQUE (hotel_id, room_type_id, accommodation_id)
);

-- ─────────────────────────────────────────────
-- Índices para mejorar rendimiento de consultas
-- ─────────────────────────────────────────────
CREATE INDEX IF NOT EXISTS idx_hotel_rooms_hotel_id
    ON hotel_rooms(hotel_id);

CREATE INDEX IF NOT EXISTS idx_hotel_rooms_room_type_id
    ON hotel_rooms(room_type_id);

CREATE INDEX IF NOT EXISTS idx_hotel_rooms_accommodation_id
    ON hotel_rooms(accommodation_id);

-- ─────────────────────────────────────────────
-- Tabla de migraciones de Laravel
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS migrations (
    id        SERIAL       PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch     INTEGER      NOT NULL
);

-- ─────────────────────────────────────────────
-- Datos iniciales: catálogos (seeders)
-- ─────────────────────────────────────────────
INSERT INTO room_types (name, created_at, updated_at) VALUES
    ('Estándar', NOW(), NOW()),
    ('Junior',   NOW(), NOW()),
    ('Suite',    NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

INSERT INTO accommodations (name, created_at, updated_at) VALUES
    ('Sencilla',  NOW(), NOW()),
    ('Doble',     NOW(), NOW()),
    ('Triple',    NOW(), NOW()),
    ('Cuádruple', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ─────────────────────────────────────────────
-- Datos de ejemplo (hotel del enunciado)
-- ─────────────────────────────────────────────
INSERT INTO hotels (name, address, city, nit, total_rooms, created_at, updated_at)
VALUES ('Decameron Cartagena', 'Calle 23 58-25', 'Cartagena', '12345678-9', 42, NOW(), NOW())
ON CONFLICT (nit) DO NOTHING;

-- Configuraciones de habitación del hotel de ejemplo
INSERT INTO hotel_rooms (hotel_id, room_type_id, accommodation_id, quantity, created_at, updated_at)
SELECT
    h.id,
    rt.id,
    a.id,
    25,
    NOW(),
    NOW()
FROM hotels h
JOIN room_types rt ON rt.name = 'Estándar'
JOIN accommodations a ON a.name = 'Sencilla'
WHERE h.nit = '12345678-9'
ON CONFLICT ON CONSTRAINT unique_hotel_room_config DO NOTHING;

INSERT INTO hotel_rooms (hotel_id, room_type_id, accommodation_id, quantity, created_at, updated_at)
SELECT
    h.id,
    rt.id,
    a.id,
    12,
    NOW(),
    NOW()
FROM hotels h
JOIN room_types rt ON rt.name = 'Junior'
JOIN accommodations a ON a.name = 'Triple'
WHERE h.nit = '12345678-9'
ON CONFLICT ON CONSTRAINT unique_hotel_room_config DO NOTHING;

INSERT INTO hotel_rooms (hotel_id, room_type_id, accommodation_id, quantity, created_at, updated_at)
SELECT
    h.id,
    rt.id,
    a.id,
    5,
    NOW(),
    NOW()
FROM hotels h
JOIN room_types rt ON rt.name = 'Estándar'
JOIN accommodations a ON a.name = 'Doble'
WHERE h.nit = '12345678-9'
ON CONFLICT ON CONSTRAINT unique_hotel_room_config DO NOTHING;

-- ─────────────────────────────────────────────
-- Registro de migraciones de Laravel
-- ─────────────────────────────────────────────
INSERT INTO migrations (migration, batch) VALUES
    ('2024_01_01_000001_create_room_types_table', 1),
    ('2024_01_01_000002_create_accommodations_table', 1),
    ('2024_01_01_000003_create_hotels_table', 1),
    ('2024_01_01_000004_create_hotel_rooms_table', 1)
ON CONFLICT DO NOTHING;
