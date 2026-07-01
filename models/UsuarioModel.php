<?php
// models/UsuarioModel.php

class UsuarioModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Busca usuario activo por username y verifica password
    public function login(int $legajo, string $password): array|false {
        $sql = "SELECT u.usuario_id, u.username, u.password_hash, u.rol, u.debe_cambiar_password,
                    e.legajo, e.nombre, e.apellido, e.depto_cod
                FROM Usuario u JOIN Empleado e ON u.legajo = e.legajo
                WHERE u.legajo = :legajo AND u.activo = 1 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':legajo' => $legajo]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            return $user;
        }
        return false;
    }

    // Activa la cuenta de un empleado ya cargado: valida legajo+mail en el SP
    // y crea el Usuario con password = legajo (nunca elegida por el que activa).
    public function activarCuenta(int $legajo, string $mail): int {
        $password_hash = password_hash((string)$legajo, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("
            CALL activar_cuenta_empleado(:legajo, :mail, :password_hash, @usuario_id)
        ");
        $stmt->execute([
            ':legajo'        => $legajo,
            ':mail'          => $mail,
            ':password_hash' => $password_hash,
        ]);
        $stmt->closeCursor(); // libera el resultado del CALL antes de leer el OUT

        return (int)$this->pdo->query("SELECT @usuario_id")->fetchColumn();
    }

    // Verifica la contraseña actual y guarda la nueva, limpiando el flag de "debe cambiar"
    public function cambiarPassword(int $usuario_id, string $actual, string $nueva): bool {
        $stmt = $this->pdo->prepare("SELECT password_hash FROM Usuario WHERE usuario_id = :id LIMIT 1");
        $stmt->execute([':id' => $usuario_id]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($actual, $hash)) {
            return false;
        }

        $nuevo_hash = password_hash($nueva, PASSWORD_DEFAULT);
        $upd = $this->pdo->prepare("
            UPDATE Usuario
            SET password_hash = :hash, debe_cambiar_password = 0
            WHERE usuario_id = :id
        ");
        $upd->execute([':hash' => $nuevo_hash, ':id' => $usuario_id]);
        return true;
    }
}