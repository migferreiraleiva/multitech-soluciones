-- =====================================================
-- BASE DE DATOS: MULTITECH SOLUCIONES (8 TABLAS EN 3FN)
-- =====================================================

CREATE TABLE estado_ticket (
    uniqueid BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE empresa (
    uniqueid BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL UNIQUE,
    rgb_color VARCHAR(20) NOT NULL,
    activa BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE cargo (
    uniqueid BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE usuario_sistema (
    uniqueid BIGSERIAL PRIMARY KEY,
    es_admin BOOLEAN NOT NULL DEFAULT FALSE,
    correo VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100) NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE usuario_empresa (
    uniqueid BIGSERIAL PRIMARY KEY,
    rut VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100) NOT NULL,
    fecha_ingreso DATE NOT NULL,
    fecha_termino DATE NULL,
    correo VARCHAR(255) NOT NULL UNIQUE,
    empresa_id BIGINT NOT NULL,
    cargo_id BIGINT NOT NULL,
    CONSTRAINT fk_usuario_empresa_empresa FOREIGN KEY (empresa_id) REFERENCES empresa(uniqueid),
    CONSTRAINT fk_usuario_empresa_cargo FOREIGN KEY (cargo_id) REFERENCES cargo(uniqueid)
);

CREATE TABLE inventario (
    uniqueid BIGSERIAL PRIMARY KEY,
    tipo_equipo VARCHAR(50) NOT NULL,
    marca VARCHAR(60) NOT NULL,
    modelo VARCHAR(80) NOT NULL,
    numero_serie VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    fecha_entrega DATE NOT NULL,
    estado_equipo VARCHAR(40) NOT NULL DEFAULT 'Asignado',
    usuario_empresa_id BIGINT NOT NULL,
    CONSTRAINT fk_inventario_usuario_empresa FOREIGN KEY (usuario_empresa_id) REFERENCES usuario_empresa(uniqueid)
);

CREATE TABLE ticket (
    uniqueid BIGSERIAL PRIMARY KEY,
    etiqueta_origen VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    fecha TIMESTAMP NOT NULL DEFAULT NOW(),
    estado_id BIGINT NOT NULL,
    usuario_empresa_id BIGINT NOT NULL,
    usuario_sistema_id BIGINT NOT NULL,
    CONSTRAINT fk_ticket_estado FOREIGN KEY (estado_id) REFERENCES estado_ticket(uniqueid),
    CONSTRAINT fk_ticket_usuario_empresa FOREIGN KEY (usuario_empresa_id) REFERENCES usuario_empresa(uniqueid),
    CONSTRAINT fk_ticket_usuario_sistema FOREIGN KEY (usuario_sistema_id) REFERENCES usuario_sistema(uniqueid)
);

CREATE TABLE evidencia (
    uniqueid BIGSERIAL PRIMARY KEY,
    imagen TEXT NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_registro TIMESTAMP NOT NULL DEFAULT NOW(),
    ticket_id BIGINT NOT NULL,
    usuario_sistema_id BIGINT NOT NULL,
    CONSTRAINT fk_evidencia_ticket FOREIGN KEY (ticket_id) REFERENCES ticket(uniqueid),
    CONSTRAINT fk_evidencia_usuario_sistema FOREIGN KEY (usuario_sistema_id) REFERENCES usuario_sistema(uniqueid)
);

-- =====================================================
-- DATOS INICIALES (CATÁLOGOS, 9 EMPRESAS Y 2 PERFILES)
-- =====================================================

INSERT INTO estado_ticket (nombre) VALUES ('Pendiente'), ('En Proceso'), ('Resuelto');

INSERT INTO cargo (nombre) VALUES ('Analista'), ('Ejecutivo Comercial'), ('Contador'), ('Gerente'), ('Soporte');

INSERT INTO empresa (nombre, rgb_color, activa) VALUES
('Wildlama', '#009b3a', TRUE),
('Marchigüe', '#ffde00', TRUE),
('Almendral', '#006837', TRUE),
('Emmu', '#1b1464', TRUE),
('Link Capital', '#2e3192', TRUE),
('Convento Viejo', '#f7931e', TRUE),
('Empresa Penta', '#f1c40f', TRUE),
('Altamarcam', '#39b54a', TRUE),
('LJTAX', '#00a99d', TRUE);

-- Contraseña para ambos perfiles: 123456
INSERT INTO usuario_sistema (es_admin, correo, password_hash, nombre, apellido_paterno, apellido_materno, activo) VALUES
(TRUE, 'admin@multitech.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Miguel', 'Ferreira', 'Leiva', TRUE),
(FALSE, 'tecnico@multitech.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos', 'Soto', 'Díaz', TRUE);