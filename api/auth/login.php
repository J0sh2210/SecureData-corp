<?php
require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../vendor/autoload.php";
require __DIR__ . "/../../services/token_service.php";
require __DIR__ . "/../../services/mail_service.php";

$json = file_get_contents("php://input");
$datos = json_decode($json, true);
header("Content-Type: application/json");

if (!isset($datos["correo"], $datos["contrasena"])) {
    echo json_encode(["success" => false, "message" => "Alguno de los campos se encuentra vacio"]);
    exit;
}

$correo = $datos["correo"];
$contrasenaIngresada = $datos["contrasena"];

$settings = json_decode(file_get_contents(__DIR__ . "/../../config/settings.json"), true);
$tokenRequired = $settings["token_required"] ?? true;

$sql = "SELECT u.IdUsuario, u.PrimerNombre, u.SegundoNombre, u.PrimerApellido, u.SegundoApellido, u.IdRol, c.Correo, c.Contrasena, c.IdCredencial
        FROM Credencial c
        JOIN Usuario u ON u.IdCredencial = c.IdCredencial
        WHERE c.Correo = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$correo]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo json_encode(["success" => false, "message" => "Credenciales incorrectas"]);
    exit;
}

if (!password_verify($contrasenaIngresada, $usuario["Contrasena"])) {
    echo json_encode(["success" => false, "message" => "Credenciales incorrectas"]);
    exit;
}

if (!$tokenRequired) {
    session_start();
    $_SESSION['idUsuario']    = (int)$usuario['IdUsuario'];
    $_SESSION['IdCredencial'] = (int)$usuario['IdCredencial'];
    $_SESSION['nombre']       = $usuario['PrimerNombre'];
    $_SESSION['apellido']     = $usuario['PrimerApellido'];
    $_SESSION['rol']          = (int)$usuario['IdRol'];
    $_SESSION['correo']       = $usuario['Correo'];
    $_SESSION['autenticado']  = true;

    echo json_encode(["success" => true, "message" => "Login exitoso", "redirect" => "../public/dashboard.php"]);
    exit;
}

try {
    $tokenService = new TokenService($conexion);
    $token = $tokenService->crearToken($usuario["IdCredencial"]);

    $config = require __DIR__ . "/../../config/correo.php";
    $mailservice = new mail_service($config);
    $mailservice->enviarLoginLink($correo, $token);

    echo json_encode(["success" => true, "message" => "Enlace de inicio de sesion generado correctamente"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "No pudo enviarse el enlace, intentelo de nuevo"]);
}
