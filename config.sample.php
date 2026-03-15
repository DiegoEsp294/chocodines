<?php
// ─── Chocodine — Configuración de ejemplo ────────────────────────────────────
// Copiá este archivo como config.php y completá con tus datos reales.
// NUNCA subas config.php al repositorio.

define('DB_HOST', 'localhost');
define('DB_USER', 'TU_USUARIO_DB');
define('DB_PASS', 'TU_CONTRASEÑA_DB');
define('DB_NAME', 'chocodine');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'uploads/');
define('ADMIN_USER', 'admin');
// Generá tu hash con: php -r "echo password_hash('TU_CONTRASEÑA', PASSWORD_DEFAULT);"
define('ADMIN_PASS', 'HASH_GENERADO_AQUI');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    $conn = null;
} else {
    $conn->set_charset('utf8mb4');
}

function getDB() {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_error) {
        die('Error de conexión: ' . $db->connect_error);
    }
    $db->set_charset('utf8mb4');
    return $db;
}
