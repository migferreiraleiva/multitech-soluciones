<?php
session_start();
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['es_admin'])) {
    header("Location: ../dashboard.php");
    exit();
}

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $nombre           = trim($_POST['nombre']);
        $apellido_paterno = trim($_POST['apellido_paterno']);
        $apellido_materno = trim($_POST['apellido_materno']);
        $correo           = trim($_POST['correo']);
        $password_hash    = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $es_admin         = ($_POST['es_admin'] === '1') ? 'true' : 'false';

        $sql = "INSERT INTO usuario_sistema (es_admin, correo, password_hash, nombre, apellido_paterno, apellido_materno, activo)
                VALUES ($es_admin, :correo, :hash, :nombre, :ap_pat, :ap_mat, TRUE)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'correo' => $correo,
            'hash'   => $password_hash,
            'nombre' => $nombre,
            'ap_pat' => $apellido_paterno,
            'ap_mat' => $apellido_materno
        ]);
    } elseif ($accion === 'cambiar_rol') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE usuario_sistema SET es_admin = NOT es_admin WHERE uniqueid = :id")->execute(['id' => $id]);
    } elseif ($accion === 'cambiar_estado') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE usuario_sistema SET activo = NOT activo WHERE uniqueid = :id")->execute(['id' => $id]);
    } elseif ($accion === 'eliminar') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM usuario_sistema WHERE uniqueid = :id")->execute(['id' => $id]);
    }

    header("Location: ../perfil_usuarios.php?ok=1");
    exit();
}