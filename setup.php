<?php
// ─── Chocodine — Database Setup (PostgreSQL) ──────────────────────────────────
// Visitá este archivo una sola vez para crear las tablas y datos de muestra.

$errors   = [];
$messages = [];

try {
    require_once __DIR__ . '/config.php';
    $db = getDB();

    // ── 1. Crear tabla products ───────────────────────────────────────────────
    $db->exec("
        CREATE TABLE IF NOT EXISTS products (
            id          SERIAL PRIMARY KEY,
            name        VARCHAR(120)  NOT NULL,
            description TEXT          NOT NULL,
            price       NUMERIC(10,2) NOT NULL DEFAULT 0,
            image       TEXT                   DEFAULT NULL,
            category    VARCHAR(20)   NOT NULL DEFAULT 'Especial'
                            CHECK (category IN ('Chocolate','Vainilla','Frutas','Especial')),
            available   SMALLINT      NOT NULL DEFAULT 1,
            created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $messages[] = 'Tabla <strong>products</strong> lista.';

    // ── 2. Insertar productos de muestra (solo si está vacía) ─────────────────
    $count = (int) $db->query("SELECT COUNT(*) FROM products")->fetchColumn();

    if ($count === 0) {
        $stmt = $db->prepare("
            INSERT INTO products (name, description, price, category, available)
            VALUES (?, ?, ?, ?, 1)
        ");

        $samples = [
            [
                'Budín de Chocolate',
                'Irresistible budín húmedo de chocolate con chips y un corazón de ganache. Textura densa y sabor profundo que enamora desde el primer bocado.',
                850.00,
                'Chocolate',
            ],
            [
                'Budín de Limón & Amapola',
                'Budín esponjoso con ralladura de limón fresco y semillas de amapola. Cubierto con glaseado cítrico artesanal. Perfecto equilibrio entre dulzura y frescura.',
                780.00,
                'Frutas',
            ],
            [
                'Budín de Vainilla & Chips',
                'Clásico budín de vainilla con chips de chocolate semi-amargo. Suave, aromático y nostálgico. El favorito de los que aman lo simple y perfecto.',
                800.00,
                'Vainilla',
            ],
        ];

        foreach ($samples as $p) {
            $stmt->execute($p);
            $messages[] = 'Producto insertado: <strong>' . htmlspecialchars($p[0]) . '</strong>';
        }
    } else {
        $messages[] = 'Los productos de muestra ya existen, no se insertaron duplicados.';
    }

} catch (Exception $e) {
    $errors[] = 'Error: ' . $e->getMessage();
}

$success = empty($errors);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chocodine — Instalación</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Dancing+Script:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <?php if ($success): ?>
    <meta http-equiv="refresh" content="4;url=index.php">
    <?php endif; ?>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Lato', sans-serif;
            background: #FFF8F0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .card {
            background: #FFFCF7;
            border: 1px solid rgba(139,69,19,0.15);
            border-radius: 18px;
            box-shadow: 0 8px 40px rgba(61,28,2,0.12);
            padding: 3rem 2.5rem;
            max-width: 560px;
            width: 100%;
            text-align: center;
        }
        .logo { font-family:'Dancing Script',cursive; font-size:2.8rem; color:#3D1C02; margin-bottom:.3rem; }
        h1 { font-family:'Playfair Display',serif; font-size:1.4rem; color:#8B4513; margin-bottom:2rem; }
        .message-list { list-style:none; text-align:left; margin-bottom:1.5rem; }
        .message-list li { padding:.5rem .75rem; border-radius:8px; margin-bottom:.4rem; font-size:.95rem; }
        .msg-ok  { background:#e6f4ea; color:#256029; border-left:3px solid #4caf50; }
        .msg-err { background:#fdecea; color:#8b2020; border-left:3px solid #e53935; }
        .redirect-note { font-size:.88rem; color:#8B4513; margin-top:1.2rem; }
        .btn { display:inline-block; margin-top:1.5rem; padding:.75rem 2rem; background:#8B4513; color:#FFF8F0; border:none; border-radius:50px; font-family:'Lato',sans-serif; font-weight:700; font-size:1rem; cursor:pointer; text-decoration:none; transition:background .2s; }
        .btn:hover { background:#3D1C02; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">Chocodine</div>
    <h1>Instalación del sistema</h1>
    <ul class="message-list">
        <?php foreach ($messages as $m): ?>
            <li class="msg-ok"><?= $m ?></li>
        <?php endforeach; ?>
        <?php foreach ($errors as $e): ?>
            <li class="msg-err"><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
    <?php if ($success): ?>
        <p class="redirect-note">Redirigiendo al inicio en unos segundos&hellip;</p>
        <a href="index.php" class="btn">Ir al inicio ahora</a>
    <?php else: ?>
        <a href="setup.php" class="btn" style="background:#c0392b;">Reintentar</a>
    <?php endif; ?>
</div>
</body>
</html>
