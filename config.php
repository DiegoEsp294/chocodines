<?php
// En Render: configurar ADMIN_USER y ADMIN_PASS como variables de entorno.
// Localmente usa estos valores por defecto (contraseña: chocodine2024).
define('ADMIN_USER', getenv('ADMIN_USER') ?: 'admin');
define('ADMIN_PASS', getenv('ADMIN_PASS') ?: '$2y$10$o7dzuUtgR37qEJ3kabUvRONdHFM/qb8v.7lkWJQ4HwK8Ux5QUlZ8a');

function getDB(): PDO {
    $url = getenv('DATABASE_URL');

    if ($url) {
        // Render provee: postgresql://user:pass@host:port/dbname
        $p   = parse_url($url);
        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s;sslmode=require',
            $p['host'],
            $p['port'] ?? 5432,
            ltrim($p['path'], '/')
        );
        $user = $p['user'];
        $pass = rawurldecode($p['pass']);
    } else {
        // Local
        $dsn  = 'pgsql:host=localhost;port=5432;dbname=chocodine';
        $user = getenv('DB_USER') ?: 'postgres';
        $pass = getenv('DB_PASS') ?: '';
    }

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

// Conexión global para index.php
try {
    $conn = getDB();
} catch (Exception $e) {
    $conn = null;
}
