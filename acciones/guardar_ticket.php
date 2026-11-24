<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresa_id         = (int)$_POST['empresa_id'];
    $etiqueta_origen    = trim($_POST['etiqueta_origen']);
    $usuario_empresa_id = (int)$_POST['usuario_empresa_id'];
    $estado_id          = (int)$_POST['estado_id'];
    $descripcion        = trim($_POST['descripcion']);
    $usuario_sistema_id = (int)$_SESSION['usuario_id'];

    $sql = "INSERT INTO ticket (etiqueta_origen, descripcion, estado_id, usuario_empresa_id, usuario_sistema_id)
            VALUES (:etiqueta, :desc, :estado, :colab_id, :tec_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'etiqueta' => $etiqueta_origen,
        'desc'     => $descripcion,
        'estado'   => $estado_id,
        'colab_id' => $usuario_empresa_id,
        'tec_id'   => $usuario_sistema_id
    ]);

    header("Location: ../empresa.php?id=" . $empresa_id . "&ok=1");
    exit();
}