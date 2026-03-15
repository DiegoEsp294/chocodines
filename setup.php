<?php
// ─── Chocodine — Database Setup ───────────────────────────────────────────────
// Visit this file once to create the database, tables and sample data.
// After running successfully it redirects to index.php.

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'chocodine');

$errors   = [];
$messages = [];

// ── 1. Connect without selecting a database ───────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) {
    $errors[] = 'No se pudo conectar al servidor MySQL: ' . $conn->connect_error;
} else {

    // ── 2. Create database ────────────────────────────────────────────────────
    $sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql)) {
        $messages[] = 'Base de datos <strong>' . DB_NAME . '</strong> lista.';
    } else {
        $errors[] = 'Error creando la base de datos: ' . $conn->error;
    }

    // ── 3. Select database ────────────────────────────────────────────────────
    $conn->select_db(DB_NAME);

    // ── 4. Create products table ──────────────────────────────────────────────
    $sql = "CREATE TABLE IF NOT EXISTS `products` (
        `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
        `name`        VARCHAR(120)     NOT NULL,
        `description` TEXT             NOT NULL,
        `price`       DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
        `image`       VARCHAR(255)              DEFAULT NULL,
        `category`    ENUM('Chocolate','Vainilla','Frutas','Especial')
                                       NOT NULL DEFAULT 'Especial',
        `available`   TINYINT(1)       NOT NULL DEFAULT 1,
        `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql)) {
        $messages[] = 'Tabla <strong>products</strong> lista.';
    } else {
        $errors[] = 'Error creando la tabla products: ' . $conn->error;
    }

    // ── 5. Insert sample products (only if table is empty) ────────────────────
    $result = $conn->query("SELECT COUNT(*) AS cnt FROM `products`");
    $row    = $result->fetch_assoc();

    if ((int)$row['cnt'] === 0) {
        $samples = [
            [
                'name'        => 'Budín de Chocolate Intenso',
                'description' => 'Irresistible budín húmedo de chocolate negro 70%, con chips de chocolate y un corazón de ganache. Textura densa y sabor profundo que enamora desde el primer bocado.',
                'price'       => 850.00,
                'category'    => 'Chocolate',
                'available'   => 1,
            ],
            [
                'name'        => 'Budín de Limón & Amapola',
                'description' => 'Budín esponjoso con ralladura de limón fresco y semillas de amapola. Cubierto con glaseado cítrico artesanal. Perfecto equilibrio entre dulzura y frescura.',
                'price'       => 780.00,
                'category'    => 'Frutas',
                'available'   => 1,
            ],
            [
                'name'        => 'Budín de Vainilla & Chips',
                'description' => 'Clásico budín de vainilla bourbon con chips de chocolate semi-amargo en cada porción. Suave, aromático y nostálgico. El favorito de los que aman lo simple y perfecto.',
                'price'       => 800.00,
                'category'    => 'Vainilla',
                'available'   => 1,
            ],
        ];

        $stmt = $conn->prepare(
            "INSERT INTO `products` (name, description, price, category, available)
             VALUES (?, ?, ?, ?, ?)"
        );

        foreach ($samples as $p) {
            $stmt->bind_param(
                'ssdsi',
                $p['name'], $p['description'], $p['price'], $p['category'], $p['available']
            );
            if ($stmt->execute()) {
                $messages[] = 'Producto de muestra insertado: <strong>' . htmlspecialchars($p['name']) . '</strong>';
            } else {
                $errors[] = 'Error insertando muestra: ' . $stmt->error;
            }
        }
        $stmt->close();
    } else {
        $messages[] = 'Los productos de muestra ya existen, no se insertaron duplicados.';
    }

    $conn->close();
}

$success = empty($errors);
// Auto-redirect after 4 seconds if everything went well
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
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(139,69,19,0.04) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(212,164,100,0.06) 0%, transparent 50%);
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

        .logo {
            font-family: 'Dancing Script', cursive;
            font-size: 2.8rem;
            color: #3D1C02;
            margin-bottom: .3rem;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: #8B4513;
            margin-bottom: 2rem;
            font-weight: 700;
        }

        .message-list {
            list-style: none;
            text-align: left;
            margin-bottom: 1.5rem;
        }

        .message-list li {
            padding: .5rem .75rem;
            border-radius: 8px;
            margin-bottom: .4rem;
            font-size: .95rem;
        }

        .msg-ok  { background: #e6f4ea; color: #256029; border-left: 3px solid #4caf50; }
        .msg-err { background: #fdecea; color: #8b2020; border-left: 3px solid #e53935; }

        .redirect-note {
            font-size: .88rem;
            color: #8B4513;
            margin-top: 1.2rem;
        }

        .btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: .75rem 2rem;
            background: #8B4513;
            color: #FFF8F0;
            border: none;
            border-radius: 50px;
            font-family: 'Lato', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: background .2s;
        }
        .btn:hover { background: #3D1C02; }
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
        <p style="color:#8b2020;font-size:.95rem;margin-bottom:1rem;">
            Corrige los errores y recarga esta página.
        </p>
        <a href="setup.php" class="btn" style="background:#c0392b;">Reintentar</a>
    <?php endif; ?>
</div>
</body>
</html>
