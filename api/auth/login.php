<?php
require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../vendor/autoload.php";
require __DIR__ . "/../../services/token_service.php";
require __DIR__ . "/../../services/mail_service.php";
$json = file_get_contents("php://input");
$datos = json_decode($json,true);
header("Content-Type: application/json");

if (!isset($datos["correo"], $datos["contrasena"])){
    echo json_encode([
        "success" => false,
        "message" => "alguno de los campos se encuentra vacio"
    ]);
    exit;
}
$correo = $datos["correo"];
$contrasenaIngresada = $datos["contrasena"];

$sql = "SELECT u.IdUsuario , u.PrimerNombre, u.PrimerApellido, u.IdRol, c.correo, c.contrasena,c.IdCredencial  FROM Credencial c 
JOIN Usuario u ON u.IdCredencial = c.IdCredencial
WHERE Correo = ?";
$stmt = $conexion ->prepare($sql);

$stmt -> execute([$correo]);
$usuario = $stmt -> fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo json_encode([
        "success" =>false,
        "message" => "Credenciales incorrectas"
    ]);
    exit;
}

if (!password_verify($contrasenaIngresada, $usuario["contrasena"])){
        echo json_encode([
        "success" =>false,
        "message" => "Credenciales incorrectas"
    ]);
    exit;
}
 try {
    $tokenService = new TokenService($conexion);
    $token = $tokenService -> crearToken($usuario["idCredencial"]);

    $config = require __DIR__ . "/../../config/correo.php";

    $mailservice = new mail_service($config);
    $mailservice -> enviarLoginLink($correo, $token);

    echo json_encode([
        "success" => true,
        "message" => "Enlace de inicio de sesion generado correctamente"
    ]);
 } catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "no pudo enviarse el enlace intentelo de nuevo"
    ]);
 }

