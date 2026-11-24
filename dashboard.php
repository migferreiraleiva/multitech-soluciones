<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/config/db.php';

// Consultar las 9 empresas activas y sus métricas en tiempo real
$sql = "
    SELECT 
        e.uniqueid,
        e.nombre,
        e.rgb_color,
        (SELECT COUNT(*) FROM usuario_empresa ue WHERE ue.empresa_id = e.uniqueid) AS total_colaboradores,
        (SELECT COUNT(*) FROM ticket t 
         INNER JOIN usuario_empresa ue ON t.usuario_empresa_id = ue.uniqueid 
         INNER JOIN estado_ticket et ON t.estado_id = et.uniqueid
         WHERE ue.empresa_id = e.uniqueid AND et.nombre != 'Resuelto') AS tickets_activos
    FROM empresa e
    WHERE e.activa = TRUE
    ORDER BY e.uniqueid ASC
";
$empresas = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MultiTech Soluciones - Dashboard Multi-Cliente</title>
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
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .navbar-brand h1 {
            margin: 0;
            font-size: 1.35rem;
        }
        .badge-db {
            background-color: #16a34a;
            color: #ffffff;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 0.9rem;
        }
        .badge-rol {
            background-color: #1e293b;
            border: 1px solid #334155;
            padding: 4px 10px;
            border-radius: 6px;
            color: #38bdf8;
            font-weight: 600;
        }
        .btn-nav {
            color: #e2e8f0;
            text-decoration: none;
            background-color: #1e293b;
            padding: 7px 14px;
            border-radius: 6px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-nav:hover {
            background-color: #334155;
        }
        .btn-logout {
            background-color: #dc2626;
            color: #ffffff;
        }
        .btn-logout:hover {
            background-color: #b91c1c;
        }
        .container {
            max-width: 1150px;
            margin: 35px auto;
            padding: 0 24px;
        }
        .header-section {
            margin-bottom: 28px;
        }
        .header-section h2 {
            margin: 0 0 6px 0;
            font-size: 1.6rem;
            color: #0f172a;
        }
        .header-section p {
            margin: 0;
            color: #475569;
        }
        .grid-empresas {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        .card-empresa {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-decoration: none;
            border-radius: 10px;
            padding: 24px;
            min-height: 135px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .card-empresa:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.18);
        }
        .card-empresa h3 {
            margin: 0;
            font-size: 1.45rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .card-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 22px;
            font-size: 0.85rem;
            font-weight: 600;
            background: rgba(0, 0, 0, 0.18);
            padding: 8px 12px;
            border-radius: 6px;
        }
        @media (max-width: 900px) {
            .grid-empresas { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .grid-empresas { grid-template-columns: 1fr; }
            .navbar { flex-direction: column; gap: 12px; text-align: center; }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="navbar-brand">
            <h1>MultiTech Soluciones</h1>
            <span class="badge-db">PostgreSQL Conectado</span>
        </div>
        <div class="navbar-user">
            <span>Hola, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></span>
            <span class="badge-rol"><?= $_SESSION['es_admin'] ? 'Administrador TI' : 'Técnico Soporte' ?></span>
            <?php if ($_SESSION['es_admin']): ?>
                <a href="perfil_usuarios.php" class="btn-nav">Usuarios TI</a>
            <?php endif; ?>
            <a href="logout.php" class="btn-nav btn-logout">Cerrar Sesión</a>
        </div>
    </header>

    <main class="container">
        <div class="header-section">
            <h2>Empresas Clientes — Panel Operativo</h2>
            <p>Seleccione una empresa cliente para gestionar su inventario de equipos, tickets de soporte y evidencias fotográficas.</p>
        </div>

        <section class="grid-empresas">
            <?php foreach ($empresas as $emp): 
                // Ajustar contraste de texto si el color de fondo es amarillo claro
                $es_claro = in_array(strtolower(trim($emp['rgb_color'])), ['#ffde00', '#f1c40f', '#ffc000', '#ffd966', '#fce4d6']);
                $color_texto = $es_claro ? '#0f172a' : '#ffffff';
                $bg_badge = $es_claro ? 'rgba(15, 23, 42, 0.12)' : 'rgba(0, 0, 0, 0.22)';
            ?>
                <a href="empresa.php?id=<?= $emp['uniqueid'] ?>" 
                   class="card-empresa" 
                   style="background-color: <?= htmlspecialchars($emp['rgb_color']) ?>; color: <?= $color_texto ?>;">
                    
                    <h3><?= htmlspecialchars($emp['nombre']) ?></h3>
                    
                    <div class="card-stats" style="background-color: <?= $bg_badge ?>;">
                        <span>Colaboradores: <?= $emp['total_colaboradores'] ?></span>
                        <span>Tickets Activos: <?= $emp['tickets_activos'] ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </section>
    </main>

</body>
</html>