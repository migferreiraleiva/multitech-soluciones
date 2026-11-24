<?php
session_start();
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['es_admin'])) {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/config/db.php';

// Obtener todas las cuentas de usuarios del sistema (Administradores y Técnicos)
$usuarios =$pdo->query("SELECT * FROM usuario_sistema ORDER BY uniqueid ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios TI - MultiTech Soluciones</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            color: #0f172a;
        }
        .navbar {
            background-color: #0f172a;
            color: #ffffff;
            padding: 14px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-volver {
            color: #ffffff;
            text-decoration: none;
            background-color: #334155;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn-volver:hover { background-color: #475569; }
        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 24px 40px 24px;
        }
        .card {
            background: #ffffff;
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            margin-bottom: 24px;
        }
        .card h2 {
            margin: 0 0 16px 0;
            font-size: 1.3rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 14px;
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 0.82rem; font-weight: 600; color: #475569; }
        .form-group input, .form-group select {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .btn-submit {
            background-color: #0f172a;
            color: #ffffff;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            align-self: end;
        }
        .btn-submit:hover { background-color: #1e293b; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        th, td {
            text-align: left;
            padding: 11px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        th { background-color: #f8fafc; color: #475569; }
        .badge-admin { background: #dbeafe; color: #1e40af; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 0.78rem; }
        .badge-tec { background: #e2e8f0; color: #334155; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 0.78rem; }
        .badge-activo { background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 0.78rem; }
        .badge-inactivo { background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 6px; font-weight: 700; font-size: 0.78rem; }
        .btn-accion {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-rol { background: #f59e0b; color: #ffffff; }
        .btn-estado { background: #3b82f6; color: #ffffff; }
        .btn-eliminar { background: #dc2626; color: #ffffff; }
        .alerta-ok {
            background: #dcfce7;
            color: #166534;
            padding: 12px 18px;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid #86efac;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="btn-volver">← Volver al Dashboard</a>
        <div>
            <span>Administrador: <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></span>
        </div>
    </nav>

    <main class="container">

        <?php if (isset($_GET['ok'])): ?>
            <div class="alerta-ok">Cuenta de usuario TI actualizada correctamente en PostgreSQL.</div>
        <?php endif; ?>

        <!-- FORMULARIO ALTA DE NUEVA CUENTA TI -->
        <section class="card">
            <h2>Registrar Nueva Cuenta de Usuario TI</h2>
            <form action="acciones/guardar_usuario_ti.php" method="POST" class="form-grid">
                <input type="hidden" name="accion" value="crear">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>
                </div>
                <div class="form-group">
                    <label>Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" required>
                </div>
                <div class="form-group">
                    <label>Apellido Materno</label>
                    <input type="text" name="apellido_materno" required>
                </div>
                <div class="form-group">
                    <label>Correo Corporativo</label>
                    <input type="email" name="correo" placeholder="nombre@multitech.cl" required>
                </div>
                <div class="form-group">
                    <label>Contraseña Inicial</label>
                    <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
                </div>
                <div class="form-group">
                    <label>Perfil / Rol</label>
                    <select name="es_admin" required>
                        <option value="0">Técnico de Soporte</option>
                        <option value="1">Administrador TI</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">+ Crear Cuenta TI</button>
            </form>
        </section>

        <!-- TABLA DE GESTIÓN DE PERFILES Y CUENTAS -->
        <section class="card">
            <h2>Cuentas Activas del Equipo MultiTech (<?= count($usuarios) ?>)</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones (Editar / Eliminar)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as$u): ?>
                        <tr>
                            <td>#<?= $u['uniqueid'] ?></td>
                            <td><strong><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido_paterno'] . ' ' .$u['apellido_materno']) ?></strong></td>
                            <td><?= htmlspecialchars($u['correo']) ?></td>
                            <td>
                                <?php if ($u['es_admin']): ?>
                                    <span class="badge-admin">Administrador TI</span>
                                <?php else: ?>
                                    <span class="badge-tec">Técnico Soporte</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['activo']): ?>
                                    <span class="badge-activo">Activo</span>
                                <?php else: ?>
                                    <span class="badge-inactivo">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td style="display: flex; gap: 6px; flex-wrap: wrap;">
                                <?php if ($u['uniqueid'] !=$_SESSION['usuario_id']): ?>
                                    <!-- Cambiar Rol -->
                                    <form action="acciones/guardar_usuario_ti.php" method="POST" style="margin:0;">
                                        <input type="hidden" name="accion" value="cambiar_rol">
                                        <input type="hidden" name="id" value="<?= $u['uniqueid'] ?>">
                                        <button type="submit" class="btn-accion btn-rol">Cambiar Rol</button>
                                    </form>
                                    <!-- Activar / Desactivar -->
                                    <form action="acciones/guardar_usuario_ti.php" method="POST" style="margin:0;">
                                        <input type="hidden" name="accion" value="cambiar_estado">
                                        <input type="hidden" name="id" value="<?= $u['uniqueid'] ?>">
                                        <button type="submit" class="btn-accion btn-estado"><?= $u['activo'] ? 'Desactivar' : 'Activar' ?></button>
                                    </form>
                                    <!-- Eliminar Usuario -->
                                    <form action="acciones/guardar_usuario_ti.php" method="POST" style="margin:0;" onsubmit="return confirm('¿Eliminar esta cuenta de usuario TI?');">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $u['uniqueid'] ?>">
                                        <button type="submit" class="btn-accion btn-eliminar">Eliminar</button>
                                    </form>
                                <?php else: ?>
                                    <small style="color: #64748b;">Sesión actual</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

    </main>

</body>
</html>