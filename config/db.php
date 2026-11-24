<?php
$database_url = getenv('DATABASE_URL');

if ($database_url) {
    $dbopts = parse_url($database_url);
    $host     = $dbopts['host'];
    $port     = $dbopts['port'] ?? 5432;
    $dbname   = ltrim($dbopts['path'], '/');
    $user     = $dbopts['user'];
    $password = $dbopts['pass'];
    $sslmode  = 'require';
} else {
    $host     = '127.0.0.1';
    $port     = '5433';
    $dbname   = 'multitech_soluciones';
    $user     = 'postgres';
    $password = 'postgres';
    $sslmode  = 'disable';
}

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=$sslmode";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error de conexión a PostgreSQL: " . $e->getMessage());
}
?>