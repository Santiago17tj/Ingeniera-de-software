-- =====================================================
-- BASE DE DATOS: Sistema de Reserva de Espacios (PHP)
-- =====================================================
-- Este archivo contiene la estructura de todas las tablas
-- Copia y pega cada sección en tu base de datos MySQL
-- =====================================================

-- Crear base de datos (si no existe)
CREATE DATABASE IF NOT EXISTS reserva_espacios CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reserva_espacios;

-- =====================================================
-- TABLA 1: usuarios (Usuarios del sistema)
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(150) NOT NULL UNIQUE,
    email VARCHAR(254) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(150) DEFAULT '',
    apellido VARCHAR(150) DEFAULT '',
    is_admin TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA 2: espacios (Espacios para reservar)
-- =====================================================
CREATE TABLE IF NOT EXISTS espacios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    tipo ENUM('SALA', 'AUDI', 'CANCHA', 'OTRO') NOT NULL DEFAULT 'SALA',
    capacidad INT UNSIGNED NOT NULL DEFAULT 1,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA 3: reservas (Reservas)
-- =====================================================
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    espacio_id INT NOT NULL,
    usuario_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado ENUM('CONFIRMADA', 'CANCELADA', 'PENDIENTE') NOT NULL DEFAULT 'CONFIRMADA',
    observaciones TEXT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (espacio_id) REFERENCES espacios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_espacio_fecha (espacio_id, fecha),
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA 4: sesiones (Sesiones de usuario)
-- =====================================================
CREATE TABLE IF NOT EXISTS sesiones (
    id VARCHAR(128) PRIMARY KEY,
    usuario_id INT,
    datos TEXT,
    fecha_expiracion DATETIME NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_expiracion (fecha_expiracion),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS DE EJEMPLO
-- =====================================================

-- Insertar usuario administrador (password: admin123)
-- NOTA: Cambia esta contraseña después de crear la base de datos
INSERT INTO usuarios (username, email, password, nombre, apellido, is_admin, is_active) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Sistema', 1, 1);

-- Insertar espacios de ejemplo
INSERT INTO espacios (nombre, tipo, capacidad, descripcion) VALUES
('Sala de Reuniones A', 'SALA', 10, 'Sala pequeña para reuniones de hasta 10 personas'),
('Sala de Reuniones B', 'SALA', 15, 'Sala mediana para reuniones de hasta 15 personas'),
('Auditorio Principal', 'AUDI', 100, 'Auditorio grande con capacidad para 100 personas'),
('Cancha de Fútbol', 'CANCHA', 22, 'Cancha de fútbol 11 para partidos'),
('Sala de Conferencias', 'SALA', 30, 'Sala grande para conferencias y presentaciones');

-- =====================================================
-- NOTAS IMPORTANTES
-- =====================================================
-- 1. La contraseña del admin por defecto es: admin123
--    CAMBIA ESTA CONTRASEÑA después de crear la base de datos
-- 2. Las contraseñas están hasheadas con bcrypt
-- 3. Para crear más usuarios, usa el sistema de registro o el panel de admin
-- 4. Asegúrate de que el usuario de la base de datos tenga permisos
--    para crear tablas y hacer INSERT, UPDATE, DELETE, SELECT
-- =====================================================
