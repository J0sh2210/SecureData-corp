<?php

require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/AuthMiddleware.php";

header("Content-Type: application/json");

$usuario = AuthMiddleware::tienePermiso("ver_perfil");

$idUsuario = $_GET["id"] ?? null;

if (!$idUsuario) {
    echo json_encode(["success" => false, "message" => "ID de usuario requerido"]);
    exit;
}

$sql = "SELECT 
    u.IdUsuario,
    u.PrimerNombre,
    u.SegundoNombre,
    u.PrimerApellido,
    u.SegundoApellido,
    c.NombreUsuario,
    c.Correo,
    r.Descripcion AS Rol
FROM Usuario u
JOIN Credencial c ON u.IdCredencial = c.IdCredencial
JOIN Rol r ON u.IdRol = r.IdRol
WHERE u.IdUsuario = ?";

$stmt = $conexion->prepare($sql);
$stmt->execute([$idUsuario]);
$usuarioEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuarioEncontrado) {
    echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
    exit;
}

echo json_encode(["success" => true, "usuario" => $usuarioEncontrado]);
