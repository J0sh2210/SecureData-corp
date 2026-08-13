<?php

class AuthMiddleware
{
    public static function autenticar(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION["autenticado"])) {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "No autenticado"]);
            exit;
        }

        return [
            "idUsuario"    => $_SESSION["idUsuario"],
            "idCredencial" => $_SESSION["IdCredencial"],
            "nombre"       => $_SESSION["nombre"],
            "apellido"     => $_SESSION["apellido"],
            "rol"          => $_SESSION["rol"],
            "correo"       => $_SESSION["correo"]
        ];
    }

    public static function requiereRol(int ...$rolesPermitidos): array
    {
        $usuario = self::autenticar();

        if (!in_array($usuario["rol"], $rolesPermitidos)) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "No tienes permisos para esta accion"]);
            exit;
        }

        return $usuario;
    }

    public static function soloAdmin(): array
    {
        return self::requiereRol(1);
    }

    public static function adminOEditor(): array
    {
        return self::requiereRol(1, 2);
    }
}
