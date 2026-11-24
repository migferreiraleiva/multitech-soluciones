<?php
session_start();
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['es_admin'])) {
    header("Location: ../dashboard.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresa_id       = (int)$_POST['empresa_id'];
    $rut              = trim($_POST['rut']);
    $nombre           = trim($_POST['nombre']);
    $apellido_paterno = trim($_POST['apellido_paterno']);
    $apellido_materno = trim($_POST['apellido_materno']);
    $correo           = trim($_POST['correo']);
    $cargo_id         = (int)$_POST['cargo_id'];
    $fecha_ingreso    = $_POST['fecha_ingreso'];

    $sql = "INSERT INTO usuario_empresa (rut, nombre, apellido_paterno, apellido_materno, fecha_ingreso, correo, empresa_id, cargo_id)
            VALUES (:rut, :nombre, :ap_pat, :ap_mat, :fecha, :correo, :emp_id, :cargo_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'rut'      => $rut,
        'nombre'   => $nombre,
        'ap_pat'   => $apellido_paterno,
        'ap_mat'   => $apellido_materno,
        'fecha'    => $fecha_ingreso,
        'correo'   => $correo,
        'emp_id'   => $empresa_id,
        'cargo_id' => $cargo_id
    ]);

    header("Location: ../empresa.php?id=" . $empresa_id . "&ok=1");
    exit();
}