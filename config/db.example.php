<?php
// config/db.example.php — Plantilla. Copiar como config/db.php y completar.

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'talenthub_db');
define('DB_USER', getenv('DB_USER') ?: 'talenthub_app');
define('DB_PASS', getenv('DB_PASS') ?: 'CAMBIAR_ESTA_PASSWORD');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true,
        ]
    );
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}