<?php
// setup_usuarios.php — Ejecutar UNA SOLA VEZ, luego borrar

require 'config/db.php';

$usuarios = [
    [1001, 'admin',    'admin123',   'Administrador'],
    [1002, 'crios',    'crios123',   'RRHH'],
    [1003, 'rsanchez', 'rsanchez123','Supervisor'],
    [1004, 'vluna',    'vluna123',   'RRHH'],
    [1005, 'dmorales', 'dmorales123','Empleado'],
    [1006, 'sparedes', 'sparedes123','Empleado'],
];

$stmt = $pdo->prepare(
    "UPDATE Usuario SET password_hash = ? WHERE legajo = ?"
);

foreach ($usuarios as $u) {
    $hash = password_hash($u[2], PASSWORD_DEFAULT);
    $stmt->execute([$hash, $u[0]]);
    echo "✓ '{$u[1]}' actualizado con hash bcrypt<br>";
}

echo "<br><strong>Listo. Borrá este archivo.</strong>";