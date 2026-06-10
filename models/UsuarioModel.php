<?php
// models/UsuarioModel.php

class UsuarioModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Busca usuario activo por username y verifica password
    public function login(string $username, string $password): array|false {
        $sql = "SELECT u.usuario_id, u.username, u.password_hash, u.rol,
                       e.legajo, e.nombre, e.apellido, e.depto_cod
                FROM   Usuario u
                JOIN   Empleado e ON u.legajo = e.legajo
                WHERE  u.username = :username
                AND    u.activo   = 1
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']); // nunca pasar el hash a la sesión
            return $user;
        }
        return false;
    }
}