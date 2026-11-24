<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$correo = trim($_POST['correo'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM usuario_sistema WHERE correo = :correo AND activo = TRUE");
$stmt->execute(['correo' => $correo]);
$usuario = $stmt->fetch();

if ($usuario && password_verify($password, $usuario['password_hash'])) {
    $_SESSION['usuario_id'] = $usuario['uniqueid'];
    $_SESSION['nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido_paterno'];
    $_SESSION['es_admin'] = (bool)$usuario['es_admin'];
    header("Location: ../dashboard.php");
    exit();
} else {
    header("Location: ../index.php?error=1");
    exit();
}
?>