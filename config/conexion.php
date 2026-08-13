<?php

class Conexion
{
    private static ?PDO $instancia = null;

    public static function obtener(): PDO
    {
        if (self::$instancia === null) {
            $host = $_ENV["DB_HOST"] ?? "localhost";
            $db   = $_ENV["DB_NAME"] ?? "SecureDataCorp";
            $user = $_ENV["DB_USER"] ?? "root";
            $pass = $_ENV["DB_PASS"] ?? "";

            try {
                self::$instancia = new PDO(
                    "mysql:host=$host;dbname=$db;charset=utf8",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    "success" => false,
                    "message" => "Error de conexion con la base de datos"
                ]);
                exit;
            }
        }

        return self::$instancia;
    }
}
