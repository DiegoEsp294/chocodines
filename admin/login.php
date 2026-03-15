<?php
session_start();
require_once __DIR__ . '/../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASS)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Usuario o contraseña incorrectos.';
}

if (!empty($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chocodine — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Dancing+Script:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Lato', sans-serif;
        background: #FFF8F0;
        background-image:
            radial-gradient(ellipse at 20% 50%, rgba(139,69,19,0.06) 0%, transparent 60%),
            radial-gradient(ellipse at 80% 20%, rgba(212,164,100,0.08) 0%, transparent 50%),
            linear-gradient(160deg, #2a1001 0%, #3D1C02 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .login-card {
        background: #FFFCF7;
        border-radius: 22px 8px 22px 8px;
        box-shadow: 0 12px 50px rgba(0,0,0,0.35);
        padding: 3.2rem 2.8rem;
        max-width: 420px;
        width: 100%;
    }

    .logo-img {
        display: block;
        width: 90px;
        height: 90px;
        object-fit: contain;
        border-radius: 50%;
        margin: 0 auto .6rem;
        border: 3px solid rgba(139,69,19,0.2);
    }
    .logo {
        font-family: 'Dancing Script', cursive;
        font-size: 3rem;
        color: #3D1C02;
        text-align: center;
        display: block;
        margin-bottom: .2rem;
    }

    .subtitle {
        text-align: center;
        font-family: 'Playfair Display', serif;
        font-size: .95rem;
        color: #8B4513;
        font-style: italic;
        margin-bottom: 2.5rem;
    }

    .form-group { margin-bottom: 1.3rem; }

    label {
        display: block;
        font-size: .82rem;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: #6b4020;
        margin-bottom: .45rem;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: .75rem 1rem;
        border: 1.5px solid #e0c8a8;
        border-radius: 10px;
        background: #FFF8F0;
        font-family: 'Lato', sans-serif;
        font-size: 1rem;
        color: #3D1C02;
        transition: border-color .2s, box-shadow .2s;
        outline: none;
    }

    input:focus {
        border-color: #8B4513;
        box-shadow: 0 0 0 3px rgba(139,69,19,0.12);
    }

    .error-msg {
        background: #fdecea;
        border-left: 3px solid #e53935;
        color: #8b2020;
        padding: .65rem .9rem;
        border-radius: 8px;
        font-size: .9rem;
        margin-bottom: 1.2rem;
    }

    .btn-login {
        width: 100%;
        padding: .9rem;
        background: #3D1C02;
        color: #FFF8F0;
        border: none;
        border-radius: 50px;
        font-family: 'Lato', sans-serif;
        font-weight: 700;
        font-size: 1rem;
        letter-spacing: .05em;
        cursor: pointer;
        margin-top: .5rem;
        transition: background .2s, transform .15s;
    }
    .btn-login:hover {
        background: #8B4513;
        transform: translateY(-2px);
    }

    .back-link {
        display: block;
        text-align: center;
        margin-top: 1.5rem;
        font-size: .85rem;
        color: #a07050;
        text-decoration: none;
        transition: color .2s;
    }
    .back-link:hover { color: #3D1C02; }
    </style>
</head>
<body>
<div class="login-card">
    <img src="../assets/logo.png" alt="Chocodine" class="logo-img">
    <span class="logo">Chocodine</span>
    <p class="subtitle">Panel de administración</p>

    <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Usuario</label>
            <input
                type="text"
                id="username"
                name="username"
                autocomplete="username"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                required
                autofocus
            >
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >
        </div>
        <button type="submit" class="btn-login">Ingresar</button>
    </form>

    <a href="../index.php" class="back-link">← Volver al sitio</a>
</div>
</body>
</html>
