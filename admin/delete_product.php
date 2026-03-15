<?php
session_start();
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../config.php';

$id = (int)($_POST['id'] ?? 0);
if (!$id) { header('Location: dashboard.php'); exit; }

$db = getDB();

// Get image filename before deleting
$stmt = $db->prepare("SELECT name, image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if ($product) {
    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);

    if (!empty($product['image']) && file_exists(UPLOAD_DIR . $product['image'])) {
        unlink(UPLOAD_DIR . $product['image']);
    }

    $_SESSION['flash'] = "Producto «{$product['name']}» eliminado.";
}
header('Location: dashboard.php');
exit;
