<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresa_id         = (int)$_POST['empresa_id'];
    $ticket_id          = (int)$_POST['ticket_id'];
    $imagen             = trim($_POST['imagen']);
    $descripcion        = trim($_POST['descripcion']);
    $usuario_sistema_id = (int)$_SESSION['usuario_id'];

    $sql = "INSERT INTO evidencia (imagen, descripcion, ticket_id, usuario_sistema_id)
            VALUES (:imagen, :desc, :ticket_id, :tec_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'imagen'    => $imagen,
        'desc'      => $descripcion,
        'ticket_id' => $ticket_id,
        'tec_id'    => $usuario_sistema_id
    ]);

    header("Location: ../empresa.php?id=" . $empresa_id . "&ok=1");
    exit();
}