<?php
// ─── Chocodine — Configuración de ejemplo ────────────────────────────────────
// Copiá este archivo como config.php y completá con tus datos.
// NUNCA subas config.php al repositorio.
//
// En Render: definí la variable de entorno DATABASE_URL (la provee el add-on
// de PostgreSQL automáticamente). No hace falta tocar nada más.
//
// En local: dejá DATABASE_URL vacía y usá las variables de abajo.

define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'uploads/');
define('ADMIN_USER', 'admin');
// Generá tu hash: php -r "echo password_hash('TU_PASS', PASSWORD_DEFAULT);"
define('ADMIN_PASS', 'HASH_GENERADO_AQUI');

function getDB(): PDO {
    $url = getenv('DATABASE_URL');

    if ($url) {
        $p    = parse_url($url);
        $host = $p['host'];
        $port = $p['port'] ?? 5432;
        $db   = ltrim($p['path'], '/');
        $user = $p['user'];
        $pass = rawurldecode($p['pass']);
        $dsn  = "pgsql:host={$host};port={$port};dbname={$db};sslmode=require";
    } else {
        $dsn  = 'pgsql:host=localhost;port=5432;dbname=chocodine';
        $user = 'TU_USUARIO_POSTGRES';
        $pass = 'TU_CONTRASEÑA_POSTGRES';
    }

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

try {
    $conn = getDB();
} catch (Exception $e) {
    $conn = null;
}
