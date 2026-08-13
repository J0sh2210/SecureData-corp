<?php

require __DIR__ . "/../../config/conexion.php";
require __DIR__ . "/../../middleware/AuthMiddleware.php";

header("Content-Type: application/json");

$usuario = AuthMiddleware::soloAdmin();

$settingsFile = __DIR__ . "/../../config/settings.json";
$settings = json_decode(file_get_contents($settingsFile), true);

echo json_encode(["success" => true, "data" => $settings]);
