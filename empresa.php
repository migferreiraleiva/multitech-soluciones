<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/config/db.php';

$empresa_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$es_admin = !empty($_SESSION['es_admin']);

// 1. Obtener datos de la empresa cliente seleccionada
$stmtEmp = $pdo->prepare("SELECT * FROM empresa WHERE uniqueid = :id AND activa = TRUE");
$stmtEmp->execute(['id' => $empresa_id]);
$empresa = $stmtEmp->fetch();

if (!$empresa) {
    header("Location: dashboard.php");
    exit();
}

// 2. Obtener lista de cargos para el formulario de colaboradores (Admin)
$cargos = $pdo->query("SELECT * FROM cargo ORDER BY nombre ASC")->fetchAll();

// 3. Obtener estados de ticket para el formulario
$estados = $pdo->query("SELECT * FROM estado_ticket ORDER BY uniqueid ASC")->fetchAll();

// 4. Obtener colaboradores de esta empresa
$stmtColab = $pdo->prepare("
    SELECT ue.*, c.nombre AS cargo_nombre 
    FROM usuario_empresa ue
    INNER JOIN cargo c ON ue.cargo_id = c.uniqueid
    WHERE ue.empresa_id = :emp_id
    ORDER BY ue.nombre ASC
");
$stmtColab->execute(['emp_id' => $empresa_id]);
$colaboradores = $stmtColab->fetchAll();

// 5. Obtener inventario asignado a los colaboradores de esta empresa
$stmtInv = $pdo->prepare("
    SELECT i.*, ue.nombre AS colab_nombre, ue.apellido_paterno AS colab_apellido, ue.rut
    FROM inventario i
    INNER JOIN usuario_empresa ue ON i.usuario_empresa_id = ue.uniqueid
    WHERE ue.empresa_id = :emp_id
    ORDER BY i.uniqueid DESC
");
$stmtInv->execute(['emp_id' => $empresa_id]);
$equipos = $stmtInv->fetchAll();

// 6. Obtener tickets asociados a esta empresa
$stmtTick = $pdo->prepare("
    SELECT t.*, et.nombre AS estado_nombre, 
           ue.nombre AS colab_nombre, ue.apellido_paterno AS colab_apellido,
           us.nombre AS tecnico_nombre, us.apellido_paterno AS tecnico_apellido
    FROM ticket t
    INNER JOIN estado_ticket et ON t.estado_id = et.uniqueid
    INNER JOIN usuario_empresa ue ON t.usuario_empresa_id = ue.uniqueid
    INNER JOIN usuario_sistema us ON t.usuario_sistema_id = us.uniqueid
    WHERE ue.empresa_id = :emp_id
    ORDER BY t.fecha DESC
");
$stmtTick->execute(['emp_id' => $empresa_id]);
$tickets = $stmtTick->fetchAll();

// 7. Obtener evidencias fotográficas de los tickets de esta empresa
$stmtEv = $pdo->prepare("
    SELECT ev.*, t.etiqueta_origen, t.descripcion AS ticket_desc,
           us.nombre AS tecnico_nombre, us.apellido_paterno AS tecnico_apellido
    FROM evidencia ev
    INNER JOIN ticket t ON ev.ticket_id = t.uniqueid
    INNER JOIN usuario_empresa ue ON t.usuario_empresa_id = ue.uniqueid
    INNER JOIN usuario_sistema us ON ev.usuario_sistema_id = us.uniqueid
    WHERE ue.empresa_id = :emp_id
    ORDER BY ev.fecha_registro DESC
");
$stmtEv->execute(['emp_id' => $empresa_id]);
$evidencias = $stmtEv->fetchAll();

// Contraste de color de cabecera según el color de la empresa
$es_claro = in_array(strtolower(trim($empresa['rgb_color'])), ['#ffde00', '#f1c40f', '#ffc000', '#ffd966', '#fce4d6']);
$color_texto_banner = $es_claro ? '#0f172a' : '#ffffff';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($empresa['nombre']) ?> - MultiTech Soluciones</title>
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
        .banner-empresa {
            padding: 26px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .banner-empresa h1 { margin: 0; font-size: 1.8rem; }
        .container {
            max-width: 1200px;
            margin: 28px auto;
            padding: 0 24px 50px 24px;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }
        .modulo-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .modulo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .modulo-header h2 { margin: 0; font-size: 1.25rem; color: #0f172a; }
        .badge-permiso {
            font-size: 0.78rem;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            background: #e2e8f0;
            color: #334155;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 0.82rem; font-weight: 600; color: #475569; }
        .form-group input, .form-group select, .form-group textarea {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.9rem;
            font-family: inherit;
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
        .tabla-responsive { overflow-x: auto; }
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
        th { background-color: #f8fafc; color: #475569; font-weight: 700; }
        .tag-estado {
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 700;
        }
        .estado-Pendiente { background: #fee2e2; color: #991b1b; }
        .estado-En { background: #fef3c7; color: #92400e; }
        .estado-Resuelto { background: #dcfce7; color: #166534; }
        .alerta-ok {
            background: #dcfce7;
            color: #166534;
            padding: 12px 18px;
            border-radius: 8px;
            font-weight: 600;
            border: 1px solid #86efac;
        }
        .grid-evidencias {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }
        .ev-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px;
            background: #f8fafc;
        }
        .ev-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 10px;
            background: #cbd5e1;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="btn-volver">← Volver a Empresas Clientes</a>
        <div>
            <span>Sesión: <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong> (<?= $es_admin ? 'Administrador TI' : 'Técnico Soporte' ?>)</span>
        </div>
    </nav>

    <header class="banner-empresa" style="background-color: <?= htmlspecialchars($empresa['rgb_color']) ?>; color: <?= $color_texto_banner ?>;">
        <div>
            <h1><?= htmlspecialchars($empresa['nombre']) ?></h1>
            <small>Panel Operativo de Inventario, Tickets por Etiqueta y Evidencia Técnica</small>
        </div>
        <div>
            <strong>ID Cliente: #<?= $empresa['uniqueid'] ?></strong>
        </div>
    </header>

    <main class="container">

        <?php if (isset($_GET['ok'])): ?>
            <div class="alerta-ok">Operación registrada correctamente en PostgreSQL.</div>
        <?php endif; ?>

        <!-- MÓDULO 1: COLABORADORES DE LA EMPRESA -->
        <section class="modulo-card">
            <div class="modulo-header">
                <h2>1. Colaboradores Registrados (<?= count($colaboradores) ?>)</h2>
                <span class="badge-permiso"><?= $es_admin ? 'Modo Administrador: CRUD Habilitado' : 'Solo Lectura (Requiere Admin para editar)' ?></span>
            </div>

            <?php if ($es_admin): ?>
                <form action="acciones/guardar_colaborador.php" method="POST" class="form-grid">
                    <input type="hidden" name="empresa_id" value="<?= $empresa_id ?>">
                    <div class="form-group">
                        <label>RUT</label>
                        <input type="text" name="rut" placeholder="12.345.678-9" required>
                    </div>
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
                        <input type="email" name="correo" required>
                    </div>
                    <div class="form-group">
                        <label>Cargo</label>
                        <select name="cargo_id" required>
                            <?php foreach ($cargos as $c): ?>
                                <option value="<?= $c['uniqueid'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha Ingreso</label>
                        <input type="date" name="fecha_ingreso" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <button type="submit" class="btn-submit">+ Registrar Colaborador</button>
                </form>
            <?php endif; ?>

            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>RUT</th>
                            <th>Nombre Completo</th>
                            <th>Cargo</th>
                            <th>Correo</th>
                            <th>Fecha Ingreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($colaboradores)): ?>
                            <tr><td colspan="5">No hay colaboradores registrados en esta empresa.</td></tr>
                        <?php else: ?>
                            <?php foreach ($colaboradores as $col): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($col['rut']) ?></strong></td>
                                    <td><?= htmlspecialchars($col['nombre'] . ' ' . $col['apellido_paterno'] . ' ' . $col['apellido_materno']) ?></td>
                                    <td><?= htmlspecialchars($col['cargo_nombre']) ?></td>
                                    <td><?= htmlspecialchars($col['correo']) ?></td>
                                    <td><?= htmlspecialchars($col['fecha_ingreso']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- MÓDULO 2: INVENTARIO DE ACTIVOS TI -->
        <section class="modulo-card">
            <div class="modulo-header">
                <h2>2. Inventario de Activos TI Asignados (<?= count($equipos) ?>)</h2>
                <span class="badge-permiso"><?= $es_admin ? 'Modo Administrador: Alta Habilitada' : 'Solo Consulta Técnica' ?></span>
            </div>

            <?php if ($es_admin && !empty($colaboradores)): ?>
                <form action="acciones/guardar_inventario.php" method="POST" class="form-grid">
                    <input type="hidden" name="empresa_id" value="<?= $empresa_id ?>">
                    <div class="form-group">
                        <label>Tipo Equipo</label>
                        <select name="tipo_equipo" required>
                            <option value="Notebook">Notebook</option>
                            <option value="Desktop">Desktop</option>
                            <option value="Monitor">Monitor</option>
                            <option value="Equipo Red">Equipo Red / Switch</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" name="marca" placeholder="Ej: Lenovo, HP, Apple" required>
                    </div>
                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" name="modelo" placeholder="Ej: ThinkPad T14" required>
                    </div>
                    <div class="form-group">
                        <label>Número de Serie</label>
                        <input type="text" name="numero_serie" placeholder="SN-XXXX" required>
                    </div>
                    <div class="form-group">
                        <label>Especificaciones</label>
                        <input type="text" name="descripcion" placeholder="RAM, SSD, Procesador" required>
                    </div>
                    <div class="form-group">
                        <label>Asignar a Colaborador</label>
                        <select name="usuario_empresa_id" required>
                            <?php foreach ($colaboradores as $col): ?>
                                <option value="<?= $col['uniqueid'] ?>"><?= htmlspecialchars($col['nombre'] . ' ' . $col['apellido_paterno']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha Entrega</label>
                        <input type="date" name="fecha_entrega" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <button type="submit" class="btn-submit">+ Asignar Equipo</button>
                </form>
            <?php endif; ?>

            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>N° Serie</th>
                            <th>Equipo</th>
                            <th>Especificaciones</th>
                            <th>Asignado a</th>
                            <th>Fecha Entrega</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($equipos)): ?>
                            <tr><td colspan="6">No hay equipos registrados para esta empresa.</td></tr>
                        <?php else: ?>
                            <?php foreach ($equipos as $eq): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($eq['numero_serie']) ?></strong></td>
                                    <td><?= htmlspecialchars($eq['tipo_equipo'] . ' ' . $eq['marca'] . ' ' . $eq['modelo']) ?></td>
                                    <td><?= htmlspecialchars($eq['descripcion']) ?></td>
                                    <td><?= htmlspecialchars($eq['colab_nombre'] . ' ' . $eq['colab_apellido']) ?></td>
                                    <td><?= htmlspecialchars($eq['fecha_entrega']) ?></td>
                                    <td><?= htmlspecialchars($eq['estado_equipo']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- MÓDULO 3: TICKETS DE SOPORTE (ENRUTAMIENTO POR ETIQUETA GMAIL) -->
        <section class="modulo-card">
            <div class="modulo-header">
                <h2>3. Tickets de Soporte (Enrutamiento por Etiqueta Gmail)</h2>
                <span class="badge-permiso">Acceso Operativo: Técnicos y Administradores</span>
            </div>

            <?php if (!empty($colaboradores)): ?>
                <form action="acciones/guardar_ticket.php" method="POST" class="form-grid">
                    <input type="hidden" name="empresa_id" value="<?= $empresa_id ?>">
                    <div class="form-group">
                        <label>Etiqueta Origen (Gmail)</label>
                        <input type="text" name="etiqueta_origen" value="<?= strtoupper(htmlspecialchars($empresa['nombre'])) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Colaborador Afectado</label>
                        <select name="usuario_empresa_id" required>
                            <?php foreach ($colaboradores as $col): ?>
                                <option value="<?= $col['uniqueid'] ?>"><?= htmlspecialchars($col['nombre'] . ' ' . $col['apellido_paterno']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado Ticket</label>
                        <select name="estado_id" required>
                            <?php foreach ($estados as $est): ?>
                                <option value="<?= $est['uniqueid'] ?>"><?= htmlspecialchars($est['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Descripción del Requerimiento / Correo Entrante</label>
                        <input type="text" name="descripcion" placeholder="Ej: Correo Gmail: Solicitud de formateo y respaldo..." required>
                    </div>
                    <button type="submit" class="btn-submit">+ Ingresar Ticket</button>
                </form>
            <?php endif; ?>

            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Etiqueta Gmail</th>
                            <th>Descripción</th>
                            <th>Colaborador</th>
                            <th>Técnico</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets)): ?>
                            <tr><td colspan="7">No hay tickets registrados para esta empresa.</td></tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $tk): 
                                $clase_estado = explode(' ', $tk['estado_nombre'])[0];
                            ?>
                                <tr>
                                    <td>#<?= $tk['uniqueid'] ?></td>
                                    <td><strong><?= htmlspecialchars($tk['etiqueta_origen']) ?></strong></td>
                                    <td><?= htmlspecialchars($tk['descripcion']) ?></td>
                                    <td><?= htmlspecialchars($tk['colab_nombre'] . ' ' . $tk['colab_apellido']) ?></td>
                                    <td><?= htmlspecialchars($tk['tecnico_nombre'] . ' ' . $tk['tecnico_apellido']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($tk['fecha'])) ?></td>
                                    <td><span class="tag-estado estado-<?= $clase_estado ?>"><?= htmlspecialchars($tk['estado_nombre']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- MÓDULO 4: EVIDENCIAS FOTOGRÁFICAS (REEMPLAZO DE SLACK) -->
        <section class="modulo-card">
            <div class="modulo-header">
                <h2>4. Bitácora y Evidencia Fotográfica (<?= count($evidencias) ?>)</h2>
                <span class="badge-permiso">Acceso Operativo: Técnicos y Administradores</span>
            </div>

            <?php if (!empty($tickets)): ?>
                <form action="acciones/guardar_evidencia.php" method="POST" class="form-grid">
                    <input type="hidden" name="empresa_id" value="<?= $empresa_id ?>">
                    <div class="form-group">
                        <label>Ticket Asociado</label>
                        <select name="ticket_id" required>
                            <?php foreach ($tickets as $tk): ?>
                                <option value="<?= $tk['uniqueid'] ?>">#<?= $tk['uniqueid'] ?> - <?= htmlspecialchars(substr($tk['descripcion'], 0, 45)) ?>...</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>URL Imagen / Evidencia</label>
                        <input type="text" name="imagen" placeholder="https://ejemplo.com/foto-equipo.jpg" required>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Detalle Técnico de la Evidencia</label>
                        <input type="text" name="descripcion" placeholder="Ej: Fotografía de placa madre y número de serie verificado" required>
                    </div>
                    <button type="submit" class="btn-submit">+ Adjuntar Evidencia</button>
                </form>
            <?php endif; ?>

            <div class="grid-evidencias">
                <?php if (empty($evidencias)): ?>
                    <p>Aún no se han adjuntado evidencias fotográficas para los tickets de esta empresa.</p>
                <?php else: ?>
                    <?php foreach ($evidencias as $ev): ?>
                        <div class="ev-card">
                            <img src="<?= htmlspecialchars($ev['imagen']) ?>" alt="Evidencia Técnica">
                            <strong>Ticket #<?= $ev['ticket_id'] ?> (<?= htmlspecialchars($ev['etiqueta_origen']) ?>)</strong>
                            <p style="font-size: 0.88rem; margin: 6px 0;"><?= htmlspecialchars($ev['descripcion']) ?></p>
                            <small style="color: #64748b;">Subido por: <?= htmlspecialchars($ev['tecnico_nombre'] . ' ' . $ev['tecnico_apellido']) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>

</body>
</html>