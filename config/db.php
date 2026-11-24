<?php
// config/db.php - Conexión PDO a PostgreSQL en Docker
$host = '127.0.0.1';
$port = '5433';
$dbname = 'multitech_soluciones';
$user = 'postgres';
$password = 'postgres';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error de conexión a PostgreSQL: " . $e->getMessage());
}
?>