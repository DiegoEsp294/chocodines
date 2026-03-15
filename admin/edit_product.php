<?php
session_start();
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: dashboard.php'); exit; }

$db = requireDB();

// Load existing product
$stmt = $db->prepare("SELECT * FROM " . TBL_PRODUCTS . " WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) { header('Location: dashboard.php'); exit; }

$errors = [];
$v = $product; // pre-fill with existing data

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = $_POST['price'] ?? '';
    $category    = $_POST['category'] ?? '';
    $available   = isset($_POST['available']) ? 1 : 0;
    $validCats   = ['Chocolate', 'Vainilla', 'Frutas', 'Especial'];

    $v = array_merge($v, compact('name', 'description', 'price', 'category', 'available'));

    if (!$name)                             $errors[] = 'El nombre es obligatorio.';
    if (!$description)                      $errors[] = 'La descripción es obligatoria.';
    if (!is_numeric($price) || $price < 0) $errors[] = 'El precio debe ser un número positivo.';
    if (!in_array($category, $validCats))  $errors[] = 'Categoría inválida.';

    $newImageData = null;
    if (!empty($_FILES['image']['name'])) {
        $file    = $_FILES['image'];
        $mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file['error'] !== UPLOAD_ERR_OK)       $errors[] = 'Error al subir la imagen.';
        elseif (!isset($mimeMap[$ext]))             $errors[] = 'Formato no permitido (jpg, jpeg, png, webp).';
        elseif ($file['size'] > 3 * 1024 * 1024)   $errors[] = 'La imagen no puede superar 3 MB.';
        else {
            $newImageData = 'data:' . $mimeMap[$ext] . ';base64,' . base64_encode(file_get_contents($file['tmp_name']));
        }
    }

    if (empty($errors)) {
        $imageName = $newImageData ?? $product['image']; // nueva imagen o conservar la existente

        $stmt = $db->prepare(
            "UPDATE " . TBL_PRODUCTS . " SET name=?, description=?, price=?, image=?, category=?, available=? WHERE id=?"
        );
        $stmt->execute([$name, $description, (float)$price, $imageName, $category, $available, $id]);

        $_SESSION['flash'] = "Producto «{$name}» actualizado correctamente.";
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar producto — Chocodine</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Dancing+Script:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
        --choco:   #3D1C02; --brown:   #8B4513;
        --caramel: #D4A464; --cream:   #FFF8F0;
        --beige:   #F5E6D3; --sidebar: #2a1001;
    }
    body { font-family:'Lato',sans-serif; background:#f0e6d8; color:var(--choco); min-height:100vh; }
    .layout { display:flex; min-height:100vh; }

    .sidebar { width:240px; min-height:100vh; background:var(--sidebar); padding:2rem 1.5rem; display:flex; flex-direction:column; flex-shrink:0; }
    .sidebar-logo { font-family:'Dancing Script',cursive; font-size:2rem; color:var(--caramel); margin-bottom:.2rem; }
    .sidebar-subtitle { font-size:.75rem; color:rgba(245,230,211,.45); letter-spacing:.08em; text-transform:uppercase; margin-bottom:2.5rem; }
    .sidebar-nav a { display:flex; align-items:center; gap:.65rem; padding:.65rem .9rem; border-radius:10px; font-size:.9rem; color:rgba(245,230,211,.7); text-decoration:none; margin-bottom:.3rem; transition:background .2s,color .2s; }
    .sidebar-nav a:hover,.sidebar-nav a.active { background:rgba(212,164,100,.15); color:var(--caramel); }
    .sidebar-footer { border-top:1px solid rgba(245,230,211,.08); padding-top:1.2rem; margin-top:auto; }
    .sidebar-footer a { display:block; font-size:.83rem; color:rgba(245,230,211,.4); text-decoration:none; margin-bottom:.5rem; transition:color .2s; }
    .sidebar-footer a:hover { color:var(--caramel); }

    .main { flex:1; padding:2.5rem 2.8rem; }
    .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem; }
    .page-title { font-family:'Playfair Display',serif; font-size:1.8rem; color:var(--choco); }

    .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.65rem 1.4rem; border-radius:50px; font-family:'Lato',sans-serif; font-weight:700; font-size:.88rem; letter-spacing:.05em; cursor:pointer; text-decoration:none; border:none; transition:background .2s,transform .15s; }
    .btn-primary { background:var(--choco); color:var(--cream); }
    .btn-primary:hover { background:var(--brown); transform:translateY(-1px); }
    .btn-secondary { background:var(--beige); color:var(--choco); }
    .btn-secondary:hover { background:#e8d4bb; }

    .flash-err { background:#fdecea; border-left:3px solid #e53935; color:#8b2020; padding:.75rem 1rem; border-radius:10px; margin-bottom:1.5rem; font-size:.92rem; }
    .flash-err div { margin-bottom:.25rem; }

    .form-card { background:var(--cream); border-radius:16px; padding:2.5rem; box-shadow:0 4px 20px rgba(61,28,2,.09); max-width:680px; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1.2rem; }
    .form-group { margin-bottom:1.4rem; }
    .form-group.full { grid-column:1/-1; }

    label { display:block; font-size:.82rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#6b4020; margin-bottom:.45rem; }
    input[type=text], input[type=number], select, textarea {
        width:100%; padding:.75rem 1rem; border:1.5px solid #e0c8a8; border-radius:10px;
        background:var(--cream); font-family:'Lato',sans-serif; font-size:.97rem; color:var(--choco);
        transition:border-color .2s,box-shadow .2s; outline:none;
    }
    input:focus, select:focus, textarea:focus { border-color:#8B4513; box-shadow:0 0 0 3px rgba(139,69,19,.12); }
    textarea { resize:vertical; min-height:100px; }

    .current-image { margin-bottom:.8rem; }
    .current-image img { border-radius:10px; max-width:160px; box-shadow:0 3px 12px rgba(61,28,2,.12); }
    .current-image p { font-size:.78rem; color:#a07050; margin-top:.35rem; }

    .file-hint { font-size:.78rem; color:#a07050; margin-top:.35rem; }
    .checkbox-wrap { display:flex; align-items:center; gap:.6rem; }
    .checkbox-wrap input[type=checkbox] { width:18px; height:18px; accent-color:var(--brown); cursor:pointer; }
    .checkbox-wrap label { margin-bottom:0; font-size:.92rem; text-transform:none; letter-spacing:0; font-weight:400; color:var(--choco); }
    .form-actions { display:flex; gap:1rem; margin-top:.5rem; flex-wrap:wrap; }

    .topbar{display:none;position:sticky;top:0;z-index:50;background:var(--sidebar);padding:.7rem 1.2rem;align-items:center;justify-content:space-between;box-shadow:0 2px 10px rgba(0,0,0,.3);}
    .topbar-logo{font-family:'Dancing Script',cursive;font-size:1.5rem;color:var(--caramel);}
    .topbar-links{display:flex;gap:.8rem;align-items:center;}
    .topbar-links a{font-size:.82rem;font-weight:700;color:rgba(245,230,211,.75);text-decoration:none;padding:.35rem .7rem;border-radius:6px;transition:background .2s,color .2s;}
    .topbar-links a:hover,.topbar-links a.btn-site{background:rgba(212,164,100,.15);color:var(--caramel);}
    @media(max-width:900px){.sidebar{display:none;}.topbar{display:flex;}.main{padding:1.5rem 1rem;}}
    @media(max-width:620px){.form-row{grid-template-columns:1fr;}}
    </style>
</head>
<body>

<div class="topbar">
    <span class="topbar-logo">Chocodine</span>
    <div class="topbar-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="../index.php" class="btn-site">← Ver sitio</a>
        <a href="logout.php">Salir</a>
    </div>
</div>

<div class="layout">
<aside class="sidebar">
    <div class="sidebar-logo">Chocodine</div>
    <p class="sidebar-subtitle">Admin Panel</p>
    <nav class="sidebar-nav">
        <a href="dashboard.php">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
            Dashboard
        </a>
        <a href="add_product.php">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
            Agregar producto
        </a>
        <a href="../index.php" target="_blank">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
            Ver sitio
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php">← Cerrar sesión</a>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <h1 class="page-title">Editar: <?= htmlspecialchars($product['name']) ?></h1>
        <a href="dashboard.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if ($errors): ?>
        <div class="flash-err">
            <?php foreach ($errors as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group full">
                    <label for="name">Nombre del producto *</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($v['name']) ?>" required maxlength="120">
                </div>
                <div class="form-group full">
                    <label for="description">Descripción *</label>
                    <textarea id="description" name="description" required><?= htmlspecialchars($v['description']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Precio (ARS) *</label>
                    <input type="number" id="price" name="price" value="<?= htmlspecialchars($v['price']) ?>" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="category">Categoría *</label>
                    <select id="category" name="category">
                        <?php foreach (['Chocolate','Vainilla','Frutas','Especial'] as $cat): ?>
                            <option value="<?= $cat ?>" <?= $v['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label for="image">Imagen del producto</label>
                    <?php if (!empty($product['image'])): ?>
                        <div class="current-image">
                            <img src="<?= $product['image'] ?>" alt="Imagen actual">
                            <p>Imagen actual · Subí una nueva para reemplazarla</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                    <p class="file-hint">JPG, PNG o WebP · Máx. 3 MB · Dejá en blanco para conservar la imagen actual</p>
                </div>
                <div class="form-group full">
                    <div class="checkbox-wrap">
                        <input type="checkbox" id="available" name="available" <?= $v['available'] ? 'checked' : '' ?>>
                        <label for="available">Producto disponible (visible en el sitio)</label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</main>
</div><!-- /.layout -->

</body>
</html>
