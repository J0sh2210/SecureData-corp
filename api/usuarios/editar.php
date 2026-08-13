<?php

require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/AuthMiddleware.php";

$json = file_get_contents("php://input");
$datos = json_decode($json, true);

$usuario = AuthMiddleware::tienePermiso("editar_usuario");

if (!$datos) {
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "message" => "JSON invalido"]);
    exit;
}

$IdUsuario = $datos["IdUsuario"] ?? null;
$IdCredencial = $datos["IdCredencial"] ?? null;

if (!$IdUsuario || !$IdCredencial) {
    header("Content-Type: application/json");
    echo json_encode(["success" => false, "message" => "IdUsuario e IdCredencial son requeridos"]);
    exit;
}

header("Content-Type: application/json");

$PrimerNombre = $datos["PrimerNombre"];
$SegundoNombre = $datos["SegundoNombre"];
$PrimerApellido = $datos["PrimerApellido"];
$SegundoApellido = $datos["SegundoApellido"];
$NombreUsuario = $datos["NombreUsuario"];
$Correo = $datos["Correo"];
$IdRol = $datos["IdRol"];

try {
    $conexion->beginTransaction();

    $sql = "UPDATE Usuario 
        SET PrimerNombre = ?, SegundoNombre = ?, PrimerApellido = ?, SegundoApellido = ?, IdRol = ?
        WHERE IdUsuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$PrimerNombre, $SegundoNombre, $PrimerApellido, $SegundoApellido, $IdRol, $IdUsuario]);

    $sql = "UPDATE Credencial SET NombreUsuario = ?, Correo = ? WHERE IdCredencial = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$NombreUsuario, $Correo, $IdCredencial]);

    $conexion->commit();
    echo json_encode(["success" => true, "message" => "Usuario editado correctamente"]);

} catch (Exception $e) {
    $conexion->rollBack();
    echo json_encode(["success" => false, "message" => "No se pudo editar el usuario"]);
}
