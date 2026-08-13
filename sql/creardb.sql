CREATE DATABASE IF NOT EXISTS SecureDataCorp;
USE SecureDataCorp;

CREATE TABLE Rol (
    IdRol INT PRIMARY KEY AUTO_INCREMENT,
    Descripcion VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE Credencial (
    IdCredencial INT PRIMARY KEY AUTO_INCREMENT,
    NombreUsuario VARCHAR(30) NOT NULL UNIQUE,
    Correo VARCHAR(50) NOT NULL UNIQUE,
    Contrasena VARCHAR(255),
    Activo BOOLEAN DEFAULT TRUE,
    FechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE LoginToken (
    IdToken INT AUTO_INCREMENT PRIMARY KEY,
    IdCredencial INT NOT NULL,
    Token VARCHAR(64) NOT NULL UNIQUE,
    FechaExpiracion DATETIME NOT NULL,
    Usado BOOLEAN DEFAULT FALSE,
    FechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (IdCredencial) REFERENCES Credencial(IdCredencial)
);

CREATE TABLE Usuario (
    IdUsuario INT AUTO_INCREMENT PRIMARY KEY,
    PrimerNombre VARCHAR(30),
    SegundoNombre VARCHAR(30) NULL,
    PrimerApellido VARCHAR(30),
    SegundoApellido VARCHAR(30),
    IdRol INT,
    IdCredencial INT,
    FOREIGN KEY (IdRol) REFERENCES Rol(IdRol) ON DELETE RESTRICT,
    FOREIGN KEY (IdCredencial) REFERENCES Credencial(IdCredencial)
);

INSERT INTO Rol (Descripcion) VALUES
('administrador'),
('editor'),
('lector');

INSERT INTO Credencial (NombreUsuario, Correo, Contrasena)
VALUES ('admin', 'admin@seguridad.com', '$2y$10$aa4LbsVISoKNmzSIkfJMc.Y32XwUa6N2beKPRUX7d6KexQI1YOmY6');

INSERT INTO Usuario (PrimerNombre, SegundoNombre, PrimerApellido, SegundoApellido, IdRol, IdCredencial)
VALUES ('Admin', '', 'Sistema', '', 1, LAST_INSERT_ID());
