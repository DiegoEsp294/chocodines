<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$name    = trim($_POST['name']    ?? '');
$comment = trim($_POST['comment'] ?? '');
$rating  = (int)($_POST['rating'] ?? 5);

if ($name === '' || $comment === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Nombre y comentario son requeridos']);
    exit;
}

if ($rating < 1 || $rating > 5) $rating = 5;
$name    = mb_substr($name,    0, 80);
$comment = mb_substr($comment, 0, 500);

try {
    $db   = getDB();
    $stmt = $db->prepare(
        "INSERT INTO chocodine_reviews (name, comment, rating) VALUES (?, ?, ?)"
    );
    $stmt->execute([$name, $comment, $rating]);
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo guardar el comentario']);
}
