<?php
// En Render: configurar ADMIN_USER y ADMIN_PASS como variables de entorno.
// Localmente usa estos valores por defecto (contraseña: chocodine2024).
define('ADMIN_USER', getenv('ADMIN_USER') ?: 'admin');
define('ADMIN_PASS', getenv('ADMIN_PASS') ?: '$2y$10$o7dzuUtgR37qEJ3kabUvRONdHFM/qb8v.7lkWJQ4HwK8Ux5QUlZ8a');

// Nombre de tabla (prefijado para convivir con otras apps en la misma BD)
define('TBL_PRODUCTS', 'chocodine_products');

function getDB(): PDO {
    $url = getenv('DATABASE_URL');

    if ($url) {
        // Render: DATABASE_URL como postgresql://user:pass@host:port/dbname
        $p   = parse_url($url);
        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s;sslmode=require',
            $p['host'],
            $p['port'] ?? 5432,
            ltrim($p['path'], '/')
        );
        $user = $p['user'];
        $pass = rawurldecode($p['pass']);
    } elseif (getenv('DB_HOST')) {
        // Render con variables individuales
        $dsn  = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
            getenv('DB_HOST'),
            getenv('DB_PORT') ?: '5432',
            getenv('DB_NAME')
        );
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
    } else {
        // Local XAMPP — MySQL
        $dsn  = 'mysql:host=localhost;dbname=chocodine;charset=utf8mb4';
        $user = 'root';
        $pass = '';
    }

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function requireDB(): PDO {
    try {
        return getDB();
    } catch (Exception $e) {
        $msg = htmlspecialchars($e->getMessage());
        die("
            <div style='font-family:sans-serif;padding:2rem;background:#fdecea;color:#8b2020;border-left:4px solid #e53935;margin:2rem;border-radius:8px;'>
                <strong>Error de conexión a la base de datos.</strong><br>
                Verificá las variables de entorno DB_HOST, DB_NAME, DB_USER, DB_PASS en Render.<br>
                <small style='opacity:.7'>{$msg}</small>
            </div>
        ");
    }
}

// Conexión global para index.php (falla silenciosamente)
try {
    $conn = getDB();
} catch (Exception $e) {
    $conn = null;
}
