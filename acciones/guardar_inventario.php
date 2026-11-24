<?php
session_start();
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['es_admin'])) {
    header("Location: ../dashboard.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresa_id         = (int)$_POST['empresa_id'];
    $tipo_equipo        = trim($_POST['tipo_equipo']);
    $marca              = trim($_POST['marca']);
    $modelo             = trim($_POST['modelo']);
    $numero_serie       = trim($_POST['numero_serie']);
    $descripcion        = trim($_POST['descripcion']);
    $usuario_empresa_id = (int)$_POST['usuario_empresa_id'];
    $fecha_entrega      = $_POST['fecha_entrega'];

    $sql = "INSERT INTO inventario (tipo_equipo, marca, modelo, numero_serie, descripcion, fecha_entrega, usuario_empresa_id)
            VALUES (:tipo, :marca, :modelo, :serie, :desc, :fecha, :colab_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'tipo'     => $tipo_equipo,
        'marca'    => $marca,
        'modelo'   => $modelo,
        'serie'    => $numero_serie,
        'desc'     => $descripcion,
        'fecha'    => $fecha_entrega,
        'colab_id' => $usuario_empresa_id
    ]);

    header("Location: ../empresa.php?id=" . $empresa_id . "&ok=1");
    exit();
}