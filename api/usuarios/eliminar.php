<?php

require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/AuthMiddleware.php";

$json = file_get_contents("php://input");
$datos = json_decode($json, true);
header("Content-Type: application/json");

$usuario = AuthMiddleware::tienePermiso("eliminar_usuario");

if (!$datos || empty($datos["IdUsuario"])) {
    echo json_encode(["success" => false, "message" => "IdUsuario requerido"]);
    exit;
}

$IdUsuario = $datos["IdUsuario"];

try {
    $sql = "SELECT IdCredencial FROM Usuario WHERE IdUsuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$IdUsuario]);
    $credencial = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$credencial) {
        echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
        exit;
    }

    $conexion->beginTransaction();

    $sql = "DELETE FROM Usuario WHERE IdUsuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$IdUsuario]);

    $sql = "DELETE FROM Credencial WHERE IdCredencial = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$credencial["IdCredencial"]]);

    $conexion->commit();
    echo json_encode(["success" => true, "message" => "Usuario eliminado correctamente"]);

} catch (Exception $e) {
    $conexion->rollBack();
    echo json_encode(["success" => false, "message" => "No se pudo eliminar el usuario"]);
}
