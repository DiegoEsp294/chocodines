<?php
session_start();
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config.php';

$db = requireDB();

// Toggle availability
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $id = (int)$_POST['toggle_id'];
    $stmt = $db->prepare("UPDATE " . TBL_PRODUCTS . " SET available = 1 - available WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: dashboard.php');
    exit;
}

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$products = $db->query("SELECT * FROM " . TBL_PRODUCTS . " ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Chocodine Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Dancing+Script:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --choco:   #3D1C02;
        --brown:   #8B4513;
        --caramel: #D4A464;
        --cream:   #FFF8F0;
        --beige:   #F5E6D3;
        --sidebar: #2a1001;
    }

    body {
        font-family: 'Lato', sans-serif;
        background: #f0e6d8;
        color: var(--choco);
        min-height: 100vh;
    }

    .layout {
        display: flex;
        min-height: 100vh;
    }

    /* ── Sidebar ── */
    .sidebar {
        width: 240px;
        min-height: 100vh;
        background: var(--sidebar);
        padding: 2rem 1.5rem;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        position: sticky;
        top: 0;
        height: 100vh;
    }

    .sidebar-logo {
        font-family: 'Dancing Script', cursive;
        font-size: 2rem;
        color: var(--caramel);
        margin-bottom: .2rem;
    }

    .sidebar-subtitle {
        font-size: .75rem;
        color: rgba(245,230,211,0.45);
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 2.5rem;
    }

    .sidebar-nav { flex: 1; }

    .sidebar-nav a {
        display: flex;
        align-items: center;
        gap: .65rem;
        padding: .65rem .9rem;
        border-radius: 10px;
        font-size: .9rem;
        color: rgba(245,230,211,0.7);
        text-decoration: none;
        margin-bottom: .3rem;
        transition: background .2s, color .2s;
    }
    .sidebar-nav a:hover,
    .sidebar-nav a.active {
        background: rgba(212,164,100,0.15);
        color: var(--caramel);
    }

    .sidebar-footer {
        border-top: 1px solid rgba(245,230,211,0.08);
        padding-top: 1.2rem;
    }

    .sidebar-footer a {
        display: block;
        font-size: .83rem;
        color: rgba(245,230,211,0.4);
        text-decoration: none;
        margin-bottom: .5rem;
        transition: color .2s;
    }
    .sidebar-footer a:hover { color: var(--caramel); }

    /* ── Main ── */
    .main {
        flex: 1;
        padding: 2.5rem 2.8rem;
        overflow-x: auto;
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        color: var(--choco);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .65rem 1.4rem;
        border-radius: 50px;
        font-family: 'Lato', sans-serif;
        font-weight: 700;
        font-size: .88rem;
        letter-spacing: .05em;
        cursor: pointer;
        text-decoration: none;
        border: none;
        transition: background .2s, transform .15s;
    }

    .btn-primary {
        background: var(--choco);
        color: var(--cream);
    }
    .btn-primary:hover { background: var(--brown); transform: translateY(-1px); }

    .btn-sm {
        padding: .38rem .85rem;
        font-size: .8rem;
    }

    .btn-edit   { background: #D4A464; color: #3D1C02; }
    .btn-edit:hover { background: #c8945a; }

    .btn-delete { background: #c0392b; color: #fff; }
    .btn-delete:hover { background: #a93226; }

    .btn-toggle-on  { background: #27ae60; color: #fff; }
    .btn-toggle-on:hover  { background: #219652; }
    .btn-toggle-off { background: #95a5a6; color: #fff; }
    .btn-toggle-off:hover { background: #7f8c8d; }

    /* Flash */
    .flash {
        padding: .75rem 1rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        font-size: .92rem;
    }
    .flash-ok  { background: #e6f4ea; color: #256029; border-left: 3px solid #4caf50; }
    .flash-err { background: #fdecea; color: #8b2020; border-left: 3px solid #e53935; }

    /* Stats row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1.2rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--cream);
        border-radius: 14px;
        padding: 1.3rem 1.5rem;
        box-shadow: 0 3px 14px rgba(61,28,2,0.08);
    }

    .stat-card .number {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: var(--brown);
        display: block;
        line-height: 1;
        margin-bottom: .25rem;
    }

    .stat-card .label {
        font-size: .8rem;
        color: #9a6535;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    /* Table */
    .table-wrap {
        background: var(--cream);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(61,28,2,0.09);
        overflow-x: auto;   /* scroll horizontal en mobile */
    }

    table {
        width: 100%;
        min-width: 640px;   /* evita que las columnas se aplasten */
        border-collapse: collapse;
    }

    thead {
        background: var(--choco);
        color: var(--caramel);
    }

    thead th {
        padding: 1rem 1.2rem;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        text-align: left;
        white-space: nowrap;
    }

    tbody tr {
        border-bottom: 1px solid rgba(61,28,2,0.06);
        transition: background .15s;
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #FFF8EE; }

    td {
        padding: .9rem 1.2rem;
        font-size: .92rem;
        vertical-align: middle;
    }

    .td-thumb {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border-radius: 8px;
        background: var(--beige);
    }

    .td-thumb-placeholder {
        width: 56px;
        height: 56px;
        background: var(--beige);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #c0a080;
        font-size: 1.4rem;
    }

    .cat-badge {
        display: inline-block;
        padding: .2rem .65rem;
        border-radius: 50px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .cat-Chocolate { background: #3D1C02; color: #F5E6D3; }
    .cat-Vainilla  { background: #D4A464; color: #3D1C02; }
    .cat-Frutas    { background: #c2775a; color: #fff8f0; }
    .cat-Especial  { background: #8B4513; color: #FFF8F0; }

    .status-dot {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .82rem;
    }
    .status-dot::before {
        content: '';
        display: inline-block;
        width: 8px; height: 8px;
        border-radius: 50%;
    }
    .status-on  { color: #27ae60; }
    .status-on::before  { background: #27ae60; }
    .status-off { color: #95a5a6; }
    .status-off::before { background: #95a5a6; }

    .td-actions {
        display: flex;
        gap: .4rem;
        flex-wrap: wrap;
    }

    .empty-row td {
        text-align: center;
        padding: 3rem;
        color: #a07050;
        font-style: italic;
    }

    /* ── Top bar (mobile) ── */
    .topbar {
        display: none;
        position: sticky;
        top: 0;
        z-index: 50;
        background: var(--sidebar);
        padding: .7rem 1.2rem;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 10px rgba(0,0,0,.3);
    }
    .topbar-logo { font-family:'Dancing Script',cursive; font-size:1.5rem; color:var(--caramel); }
    .topbar-links { display:flex; gap:.8rem; align-items:center; }
    .topbar-links a { font-size:.82rem; font-weight:700; color:rgba(245,230,211,.75); text-decoration:none; padding:.35rem .7rem; border-radius:6px; transition:background .2s,color .2s; }
    .topbar-links a:hover { background:rgba(212,164,100,.15); color:var(--caramel); }
    .topbar-links a.btn-site { background:rgba(212,164,100,.2); color:var(--caramel); }

    @media (max-width: 900px) {
        .sidebar { display: none; }
        .topbar  { display: flex; }
        .main { padding: 1.5rem 1rem; }
    }
    </style>
</head>
<body>

<div class="topbar">
    <span class="topbar-logo">Chocodine</span>
    <div class="topbar-links">
        <a href="add_product.php">+ Agregar</a>
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
        <a href="dashboard.php" class="active">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
            Dashboard
        </a>
        <a href="add_product.php">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
            Agregar producto
        </a>
        <a href="../index.php" target="_blank" style="background:rgba(212,164,100,.15);color:var(--caramel);margin-top:.5rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
            ← Ver sitio
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:.3rem;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            Cerrar sesión
        </a>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <h1 class="page-title">Productos</h1>
        <a href="add_product.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Agregar producto
        </a>
    </div>

    <?php if ($flash): ?>
        <div class="flash flash-ok"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <?php
    $total     = count($products);
    $available = count(array_filter($products, fn($p) => $p['available']));
    $unavail   = $total - $available;
    ?>
    <div class="stats-row">
        <div class="stat-card">
            <span class="number"><?= $total ?></span>
            <span class="label">Total productos</span>
        </div>
        <div class="stat-card">
            <span class="number" style="color:#27ae60"><?= $available ?></span>
            <span class="label">Disponibles</span>
        </div>
        <div class="stat-card">
            <span class="number" style="color:#95a5a6"><?= $unavail ?></span>
            <span class="label">Ocultos</span>
        </div>
    </div>

    <!-- Table -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($products)): ?>
                <tr class="empty-row">
                    <td colspan="6">No hay productos todavía. <a href="add_product.php">Agregar el primero</a></td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <?php if (!empty($p['image'])): ?>
                            <img src="<?= $p['image'] ?>" alt="" class="td-thumb">
                        <?php else: ?>
                            <div class="td-thumb-placeholder">🍰</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($p['name']) ?></strong>
                        <br>
                        <small style="color:#a07050;font-size:.78rem;">
                            <?= htmlspecialchars(mb_strimwidth($p['description'], 0, 60, '…')) ?>
                        </small>
                    </td>
                    <td>
                        <span class="cat-badge cat-<?= htmlspecialchars($p['category']) ?>">
                            <?= htmlspecialchars($p['category']) ?>
                        </span>
                    </td>
                    <td><strong>$<?= number_format($p['price'], 0, ',', '.') ?></strong></td>
                    <td>
                        <span class="status-dot <?= $p['available'] ? 'status-on' : 'status-off' ?>">
                            <?= $p['available'] ? 'Activo' : 'Oculto' ?>
                        </span>
                    </td>
                    <td>
                        <div class="td-actions">
                            <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-edit">Editar</a>

                            <form method="POST" action="" style="display:inline">
                                <input type="hidden" name="toggle_id" value="<?= $p['id'] ?>">
                                <button
                                    type="submit"
                                    class="btn btn-sm <?= $p['available'] ? 'btn-toggle-on' : 'btn-toggle-off' ?>"
                                    title="<?= $p['available'] ? 'Ocultar' : 'Mostrar' ?>"
                                >
                                    <?= $p['available'] ? 'Ocultar' : 'Mostrar' ?>
                                </button>
                            </form>

                            <form method="POST" action="delete_product.php" style="display:inline"
                                  onsubmit="return confirm('¿Eliminar «<?= htmlspecialchars(addslashes($p['name'])) ?>»? Esta acción no se puede deshacer.')">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-delete">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</div><!-- /.layout -->

</body>
</html>
