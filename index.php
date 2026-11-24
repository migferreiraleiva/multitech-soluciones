<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MultiTech Soluciones</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="login-box">
        <h2>MultiTech Soluciones</h2>
        <p>Portal de Soporte Técnico e Inventario TI</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="alerta-error">Correo o contraseña incorrectos.</div>
        <?php endif; ?>

        <form action="acciones/login_procesar.php" method="POST">
            <div class="form-grupo">
                <label>Correo Corporativo</label>
                <input type="email" name="correo" placeholder="admin@multitech.cl" required>
            </div>
            <div class="form-grupo">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="••••••" required>
            </div>
            <button type="submit" class="btn btn-full">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>