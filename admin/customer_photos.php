<?php
session_start();
if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/../config.php';
$db = requireDB();

$errors = [];

// ── Upload ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $caption = mb_substr(trim($_POST['caption'] ?? ''), 0, 140) ?: null;

    if (empty($_FILES['photo']['name'])) {
        $errors[] = 'Seleccioná una foto.';
    } else {
        $file    = $_FILES['photo'];
        $mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK)     $errors[] = 'Error al subir la imagen.';
        elseif (!isset($mimeMap[$ext]))            $errors[] = 'Formato no permitido (jpg, png, webp).';
        elseif ($file['size'] > 4 * 1024 * 1024)  $errors[] = 'La imagen no puede superar 4 MB.';
        else {
            $imageData = 'data:' . $mimeMap[$ext] . ';base64,' . base64_encode(file_get_contents($file['tmp_name']));
            $db->prepare("INSERT INTO chocodine_customer_photos (image, caption) VALUES (?, ?)")
               ->execute([$imageData, $caption]);
            $_SESSION['flash'] = 'Foto agregada correctamente.';
            header('Location: customer_photos.php'); exit;
        }
    }
}

// ── Delete ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['photo_id'];
    $db->prepare("DELETE FROM chocodine_customer_photos WHERE id = ?")->execute([$id]);
    $_SESSION['flash'] = 'Foto eliminada.';
    header('Location: customer_photos.php'); exit;
}

$flash  = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$photos = $db->query("SELECT * FROM chocodine_customer_photos ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fotos de clientes — Chocodine Admin</title>
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

    .sidebar { width:240px; min-height:100vh; background:var(--sidebar); padding:2rem 1.5rem; display:flex; flex-direction:column; flex-shrink:0; position:sticky; top:0; height:100vh; }
    .sidebar-logo { font-family:'Dancing Script',cursive; font-size:2rem; color:var(--caramel); margin-bottom:.2rem; }
    .sidebar-subtitle { font-size:.75rem; color:rgba(245,230,211,.45); letter-spacing:.08em; text-transform:uppercase; margin-bottom:2.5rem; }
    .sidebar-nav { flex:1; }
    .sidebar-nav a { display:flex; align-items:center; gap:.65rem; padding:.65rem .9rem; border-radius:10px; font-size:.9rem; color:rgba(245,230,211,.7); text-decoration:none; margin-bottom:.3rem; transition:background .2s,color .2s; }
    .sidebar-nav a:hover, .sidebar-nav a.active { background:rgba(212,164,100,.15); color:var(--caramel); }
    .sidebar-footer { border-top:1px solid rgba(245,230,211,.08); padding-top:1.2rem; }
    .sidebar-footer a { display:block; font-size:.83rem; color:rgba(245,230,211,.4); text-decoration:none; margin-bottom:.5rem; transition:color .2s; }
    .sidebar-footer a:hover { color:var(--caramel); }

    .main { flex:1; padding:2.5rem 2.8rem; overflow-x:auto; }
    .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem; }
    .page-title { font-family:'Playfair Display',serif; font-size:1.8rem; color:var(--choco); }

    .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.65rem 1.4rem; border-radius:50px; font-family:'Lato',sans-serif; font-weight:700; font-size:.88rem; letter-spacing:.05em; cursor:pointer; text-decoration:none; border:none; transition:background .2s,transform .15s; }
    .btn-primary   { background:var(--choco); color:var(--cream); }
    .btn-primary:hover   { background:var(--brown); transform:translateY(-1px); }
    .btn-secondary { background:var(--beige); color:var(--choco); }
    .btn-secondary:hover { background:#e8d4bb; }
    .btn-delete    { background:#fdecea; color:#c0392b; }
    .btn-delete:hover    { background:#fbc7c7; }

    .flash-ok  { background:#e6f4ea; border-left:3px solid #4caf50; color:#256029; padding:.75rem 1rem; border-radius:10px; margin-bottom:1.5rem; font-size:.92rem; }
    .flash-err { background:#fdecea; border-left:3px solid #e53935; color:#8b2020; padding:.75rem 1rem; border-radius:10px; margin-bottom:1.5rem; font-size:.92rem; }
    .flash-err div { margin-bottom:.25rem; }

    /* Upload form */
    .upload-card { background:var(--cream); border-radius:16px; padding:2rem 2.4rem; box-shadow:0 4px 20px rgba(61,28,2,.09); max-width:560px; margin-bottom:3rem; }
    .upload-card h2 { font-family:'Playfair Display',serif; font-size:1.2rem; color:var(--choco); margin-bottom:1.4rem; }
    .form-group { margin-bottom:1.1rem; }
    label { display:block; font-size:.82rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#6b4020; margin-bottom:.45rem; }
    input[type=text], input[type=file] { width:100%; padding:.7rem 1rem; border:1.5px solid #e0c8a8; border-radius:10px; background:var(--cream); font-family:'Lato',sans-serif; font-size:.95rem; color:var(--choco); transition:border-color .2s; outline:none; }
    input:focus { border-color:#8B4513; }
    .file-hint { font-size:.78rem; color:#a07050; margin-top:.3rem; }

    /* Photos grid */
    .photos-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:1.2rem; }
    .photo-item { background:var(--cream); border-radius:14px; overflow:hidden; box-shadow:0 3px 14px rgba(61,28,2,.1); }
    .photo-item img { width:100%; aspect-ratio:1; object-fit:cover; display:block; }
    .photo-item-body { padding:.8rem 1rem; display:flex; flex-direction:column; gap:.5rem; }
    .photo-caption { font-size:.82rem; color:#6b4020; line-height:1.45; min-height:1.2em; font-style:italic; }
    .photo-date { font-size:.75rem; color:#a07050; }

    .topbar{display:none;position:sticky;top:0;z-index:50;background:var(--sidebar);padding:.7rem 1.2rem;align-items:center;justify-content:space-between;box-shadow:0 2px 10px rgba(0,0,0,.3);}
    .topbar-logo{font-family:'Dancing Script',cursive;font-size:1.5rem;color:var(--caramel);}
    .topbar-links{display:flex;gap:.8rem;align-items:center;}
    .topbar-links a{font-size:.82rem;font-weight:700;color:rgba(245,230,211,.75);text-decoration:none;padding:.35rem .7rem;border-radius:6px;transition:background .2s,color .2s;}
    .topbar-links a:hover,.topbar-links a.btn-site{background:rgba(212,164,100,.15);color:var(--caramel);}
    @media(max-width:900px){.sidebar{display:none;}.topbar{display:flex;}.main{padding:1.5rem 1rem;}}
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
    <img src="../assets/logo.png" alt="Chocodine" style="width:72px;height:72px;object-fit:contain;border-radius:50%;display:block;margin:0 auto 1rem;border:2px solid rgba(212,164,100,0.25);">
    <div class="sidebar-logo" style="text-align:center;">Chocodine</div>
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
        <a href="customer_photos.php" class="active">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="3"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
            Fotos de clientes
        </a>
        <a href="../index.php" target="_blank" style="background:rgba(212,164,100,.15);color:var(--caramel);margin-top:.5rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
            ← Ver sitio
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php">← Cerrar sesión</a>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <h1 class="page-title">Fotos de clientes</h1>
        <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
    </div>

    <?php if ($flash): ?>
        <div class="flash-ok"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="flash-err"><?php foreach ($errors as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <!-- Upload form -->
    <div class="upload-card">
        <h2>Subir nueva foto</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">
            <div class="form-group">
                <label for="photo">Foto del cliente *</label>
                <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" required>
                <p class="file-hint">JPG, PNG o WebP · Máx. 4 MB</p>
            </div>
            <div class="form-group">
                <label for="caption">Leyenda (opcional)</label>
                <input type="text" id="caption" name="caption" maxlength="140" placeholder="Ej: "¡Riquísimo el de chocolate!" — Laura">
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:.5rem;">Subir foto</button>
        </form>
    </div>

    <!-- Photos grid -->
    <?php if (empty($photos)): ?>
        <p style="color:#a07050;font-style:italic;">Aún no hay fotos. Subí la primera arriba.</p>
    <?php else: ?>
    <div class="photos-grid">
        <?php foreach ($photos as $ph): ?>
        <div class="photo-item">
            <img src="<?= $ph['image'] ?>" alt="Foto de cliente">
            <div class="photo-item-body">
                <?php if ($ph['caption']): ?>
                    <p class="photo-caption"><?= htmlspecialchars($ph['caption']) ?></p>
                <?php endif; ?>
                <span class="photo-date"><?= date('d/m/Y', strtotime($ph['created_at'])) ?></span>
                <form method="POST" onsubmit="return confirm('¿Eliminar esta foto?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="photo_id" value="<?= $ph['id'] ?>">
                    <button type="submit" class="btn btn-delete" style="width:100%;justify-content:center;padding:.45rem;">Eliminar</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>
</div>

</body>
</html>
