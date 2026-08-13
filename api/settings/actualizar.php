<?php

require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/AuthMiddleware.php";

header("Content-Type: application/json");

$usuario = AuthMiddleware::soloAdmin();

$json = file_get_contents("php://input");
$datos = json_decode($json, true);

if (!$datos || !isset($datos["token_required"])) {
    echo json_encode(["success" => false, "message" => "token_required es requerido"]);
    exit;
}

$settingsFile = __DIR__ . "/../../config/settings.json";
$settings = ["token_required" => (bool) $datos["token_required"]];
file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT));

echo json_encode(["success" => true, "message" => "Configuracion actualizada", "data" => $settings]);
