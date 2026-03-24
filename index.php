<?php
require_once __DIR__ . '/config.php';

// ── Fetch products ────────────────────────────────────────────────────────────
$products = [];
if ($conn) {
    try {
        $res = $conn->query(
            "SELECT * FROM " . TBL_PRODUCTS . " WHERE available = 1 ORDER BY created_at DESC"
        );
        $products = $res->fetchAll();
    } catch (Exception $e) {
        // tabla aún no creada — redirigir a setup
    }
}

$customer_photos = [];
if ($conn) {
    try {
        $res = $conn->query(
            "SELECT image, caption FROM chocodine_customer_photos ORDER BY created_at DESC LIMIT 24"
        );
        $customer_photos = $res->fetchAll();
    } catch (Exception $e) { /* tabla aún no creada */ }
}

$reviews = [];
if ($conn) {
    try {
        $res = $conn->query(
            "SELECT name, comment, rating, created_at FROM chocodine_reviews
             WHERE approved = 1 ORDER BY created_at DESC LIMIT 20"
        );
        $reviews = $res->fetchAll();
    } catch (Exception $e) {
        // tabla aún no creada
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chocodine — Budines Artesanales</title>
    <meta name="description" content="Budines artesanales elaborados con amor. Chocolate, vainilla, frutas y sabores especiales. Pedidos por WhatsApp.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Dancing+Script:wght@600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <style>
    /* ═══════════════════════════════════════════════════════════════════════════
       RESET & BASE
    ═══════════════════════════════════════════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --choco:    #3D1C02;
        --brown:    #8B4513;
        --caramel:  #D4A464;
        --cream:    #FFF8F0;
        --beige:    #F5E6D3;
        --warm-shadow: rgba(61,28,2,0.14);
    }

    html { scroll-behavior: smooth; }

    body {
        font-family: 'Lato', sans-serif;
        background-color: var(--cream);
        color: var(--choco);
        line-height: 1.7;
        /* Subtle kraft-paper texture via layered radial gradients */
        background-image:
            radial-gradient(ellipse at 15% 25%, rgba(212,164,100,0.07) 0%, transparent 55%),
            radial-gradient(ellipse at 85% 70%, rgba(139,69,19,0.05) 0%, transparent 55%),
            radial-gradient(circle at 50% 50%, rgba(245,230,211,0.5) 0%, transparent 80%);
    }

    img { display: block; max-width: 100%; }
    a   { color: inherit; text-decoration: none; }

    /* ═══════════════════════════════════════════════════════════════════════════
       NAVIGATION
    ═══════════════════════════════════════════════════════════════════════════ */
    nav {
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 100;
        background: rgba(61,28,2,0.92);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .85rem 2.5rem;
        box-shadow: 0 2px 20px rgba(0,0,0,0.25);
    }

    .nav-logo {
        display: flex;
        align-items: center;
        gap: .6rem;
        font-family: 'Dancing Script', cursive;
        font-size: 2rem;
        color: var(--caramel);
        letter-spacing: .03em;
    }

    .nav-logo img {
        height: 44px;
        width: 44px;
        object-fit: contain;
        border-radius: 50%;
        border: 2px solid rgba(212,164,100,0.4);
    }

    .nav-links {
        display: flex;
        gap: 2rem;
        list-style: none;
    }

    .nav-links a {
        font-family: 'Lato', sans-serif;
        font-size: .92rem;
        font-weight: 700;
        color: var(--beige);
        letter-spacing: .08em;
        text-transform: uppercase;
        transition: color .2s;
        position: relative;
    }

    .nav-links a::after {
        content: '';
        position: absolute;
        bottom: -3px; left: 0; right: 0;
        height: 2px;
        background: var(--caramel);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform .25s;
    }

    .nav-links a:hover { color: var(--caramel); }
    .nav-links a:hover::after { transform: scaleX(1); }

    /* Hamburger */
    .nav-toggle {
        display: none;
        background: none;
        border: none;
        cursor: pointer;
        padding: .3rem;
        flex-direction: column;
        gap: 5px;
    }
    .nav-toggle span {
        display: block;
        width: 26px; height: 2px;
        background: var(--caramel);
        border-radius: 2px;
        transition: .3s;
    }

    /* ═══════════════════════════════════════════════════════════════════════════
       HERO
    ═══════════════════════════════════════════════════════════════════════════ */
    #hero {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 7rem 2rem 4rem;
        position: relative;
        overflow: hidden;

        background:
            radial-gradient(ellipse at 30% 60%, rgba(139,69,19,0.45) 0%, transparent 55%),
            radial-gradient(ellipse at 75% 30%, rgba(61,28,2,0.6) 0%, transparent 55%),
            linear-gradient(160deg, #2a1001 0%, #3D1C02 35%, #6B3010 65%, #8B4513 100%);
    }

    /* Decorative circles */
    #hero::before,
    #hero::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        opacity: .08;
        background: var(--caramel);
    }
    #hero::before { width: 600px; height: 600px; top: -200px; right: -150px; }
    #hero::after  { width: 400px; height: 400px; bottom: -120px; left: -100px; }

    .hero-content { position: relative; z-index: 1; max-width: 720px; }

    .hero-eyebrow {
        font-family: 'Dancing Script', cursive;
        font-size: 1.4rem;
        color: var(--caramel);
        margin-bottom: .6rem;
        opacity: .9;
    }

    .hero-logo-img {
        width: clamp(180px, 40vw, 280px);
        height: clamp(180px, 40vw, 280px);
        object-fit: contain;
        border-radius: 50%;
        box-shadow: 0 8px 48px rgba(0,0,0,0.45), 0 0 0 6px rgba(212,164,100,0.25);
        margin: 0 auto 1.2rem;
        display: block;
        animation: float 4s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50%       { transform: translateY(-10px); }
    }

    .hero-logo {
        font-family: 'Dancing Script', cursive;
        font-size: clamp(4rem, 12vw, 8rem);
        color: var(--cream);
        line-height: 1;
        text-shadow: 0 4px 32px rgba(0,0,0,0.4);
        margin-bottom: .5rem;
    }

    .hero-tagline {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.1rem, 3vw, 1.55rem);
        color: var(--caramel);
        font-style: italic;
        margin-bottom: 2.5rem;
        letter-spacing: .04em;
    }

    .hero-cta {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
        padding: 1rem 2.4rem;
        background: var(--caramel);
        color: var(--choco);
        border-radius: 50px;
        font-family: 'Lato', sans-serif;
        font-weight: 700;
        font-size: 1rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        box-shadow: 0 6px 24px rgba(212,164,100,0.35);
        transition: transform .2s, box-shadow .2s, background .2s;
    }
    .hero-cta:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 32px rgba(212,164,100,0.45);
        background: #e0b578;
    }

    .hero-scroll {
        position: absolute;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .4rem;
        opacity: .55;
        animation: bounce 2s infinite;
    }
    .hero-scroll span {
        font-size: .7rem;
        color: var(--beige);
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .hero-scroll svg { color: var(--caramel); }

    @keyframes bounce {
        0%, 100% { transform: translateX(-50%) translateY(0); }
        50%       { transform: translateX(-50%) translateY(8px); }
    }

    /* ═══════════════════════════════════════════════════════════════════════════
       SECTION COMMONS
    ═══════════════════════════════════════════════════════════════════════════ */
    section { padding: 6rem 2rem; scroll-margin-top: 70px; }

    .section-inner {
        max-width: 1100px;
        margin: 0 auto;
    }

    .section-label {
        font-family: 'Dancing Script', cursive;
        font-size: 1.25rem;
        color: var(--brown);
        display: block;
        margin-bottom: .3rem;
        opacity: .8;
    }

    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: clamp(2rem, 5vw, 3rem);
        color: var(--choco);
        line-height: 1.15;
        margin-bottom: .8rem;
    }

    .section-divider {
        width: 60px;
        height: 3px;
        background: var(--caramel);
        border-radius: 2px;
        margin: 0 0 3rem 0;
    }

    /* ═══════════════════════════════════════════════════════════════════════════
       PRODUCTS GRID
    ═══════════════════════════════════════════════════════════════════════════ */
    #productos { background: var(--beige); }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
        gap: 2.5rem;
        align-items: start;
    }

    /* Alternating slight rotation for handmade feel */
    .product-card:nth-child(odd)  { transform: rotate(-0.8deg); }
    .product-card:nth-child(even) { transform: rotate(0.7deg);  }

    .product-card {
        background: var(--cream);
        border-radius: 22px 8px 22px 8px; /* organic / slightly asymmetric */
        overflow: hidden;
        box-shadow:
            0 4px 16px var(--warm-shadow),
            0 1px 3px rgba(0,0,0,0.06);
        transition: transform .3s, box-shadow .3s;
        position: relative;
    }

    .product-card:hover {
        transform: rotate(0deg) translateY(-6px) !important;
        box-shadow:
            0 12px 40px rgba(61,28,2,0.18),
            0 2px 6px rgba(0,0,0,0.08);
    }

    .product-img-wrap {
        width: 100%;
        aspect-ratio: 4/3;
        overflow: hidden;
        background: var(--beige);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .product-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .4s;
    }
    .product-card:hover .product-img-wrap img { transform: scale(1.05); }

    .product-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        color: var(--brown);
        opacity: .45;
        background: linear-gradient(135deg, #f0dfc8, #e8d0b5);
    }
    .product-placeholder svg { opacity: .6; }
    .product-placeholder span { font-size: .8rem; font-family: 'Lato', sans-serif; }

    .product-body { padding: 1.4rem 1.5rem 1.8rem; }

    .product-category {
        display: inline-block;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        padding: .22rem .7rem;
        border-radius: 50px;
        margin-bottom: .8rem;
    }

    .cat-Chocolate { background: #3D1C02; color: #F5E6D3; }
    .cat-Vainilla  { background: #D4A464; color: #3D1C02; }
    .cat-Frutas    { background: #c2775a; color: #fff8f0; }
    .cat-Especial  { background: #8B4513; color: #FFF8F0; }

    .product-name {
        font-family: 'Playfair Display', serif;
        font-size: 1.2rem;
        color: var(--choco);
        margin-bottom: .5rem;
        line-height: 1.3;
    }

    .product-desc {
        font-size: .9rem;
        color: #7a5a3a;
        line-height: 1.6;
        margin-bottom: 1.1rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }

    .product-price {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        color: var(--brown);
        font-weight: 700;
    }
    .product-price small {
        font-family: 'Lato', sans-serif;
        font-size: .8rem;
        color: #a07050;
        font-weight: 400;
    }

    .btn-pedido {
        padding: .55rem 1.2rem;
        background: var(--choco);
        color: var(--cream);
        border-radius: 50px;
        font-size: .82rem;
        font-weight: 700;
        letter-spacing: .05em;
        transition: background .2s, transform .15s;
        white-space: nowrap;
    }
    .btn-pedido:hover {
        background: var(--brown);
        transform: scale(1.04);
    }

    /* ── Botón descargar story ─────────────────────────────────────────────── */
    .btn-download-story {
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(26, 8, 0, 0.68);
        border: 1.5px solid rgba(212,164,100,0.45);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        transition: background .2s, transform .15s, border-color .2s;
        z-index: 3;
        color: #F5E6D3;
        padding: 0;
    }
    .btn-download-story:hover {
        background: rgba(26, 8, 0, 0.92);
        border-color: rgba(212,164,100,0.8);
        transform: scale(1.12);
    }
    .btn-download-story svg { pointer-events: none; }

    .btn-download-story .dl-tooltip {
        position: absolute;
        bottom: calc(100% + 8px);
        right: 0;
        background: rgba(26,8,0,0.9);
        color: #F5E6D3;
        font-family: 'Lato', sans-serif;
        font-size: .72rem;
        white-space: nowrap;
        padding: .3rem .6rem;
        border-radius: 6px;
        pointer-events: none;
        opacity: 0;
        transform: translateY(4px);
        transition: opacity .2s, transform .2s;
        letter-spacing: .03em;
    }
    .btn-download-story:hover .dl-tooltip {
        opacity: 1;
        transform: translateY(0);
    }

    .empty-products {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--brown);
        grid-column: 1/-1;
    }
    .empty-products p { font-family: 'Playfair Display', serif; font-size: 1.2rem; }

    /* ═══════════════════════════════════════════════════════════════════════════
       ABOUT
    ═══════════════════════════════════════════════════════════════════════════ */
    #nosotros {
        background: var(--cream);
        position: relative;
        overflow: hidden;
    }

    #nosotros::before {
        content: '';
        position: absolute;
        right: -120px; top: -80px;
        width: 500px; height: 500px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(212,164,100,0.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .about-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        align-items: center;
    }

    .about-text p {
        color: #6b4423;
        margin-bottom: 1.2rem;
        font-size: 1.02rem;
    }

    .about-values {
        list-style: none;
        margin-top: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: .7rem;
    }

    .about-values li {
        display: flex;
        align-items: flex-start;
        gap: .8rem;
        font-size: .95rem;
        color: #7a5230;
    }

    .about-values li .icon {
        flex-shrink: 0;
        width: 28px; height: 28px;
        background: var(--caramel);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        margin-top: .05rem;
    }

    .about-visual {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
    }

    .about-quote-card {
        background: var(--choco);
        color: var(--cream);
        border-radius: 20px 6px 20px 6px;
        padding: 2rem 2.2rem;
        position: relative;
        box-shadow: 0 8px 32px rgba(61,28,2,0.2);
    }

    .about-quote-card blockquote {
        font-family: 'Playfair Display', serif;
        font-style: italic;
        font-size: 1.15rem;
        line-height: 1.6;
        color: var(--caramel);
        margin-bottom: .8rem;
    }

    .about-quote-card cite {
        font-size: .85rem;
        color: var(--beige);
        opacity: .7;
        font-style: normal;
    }

    .about-stat-row {
        display: flex;
        gap: 1rem;
    }

    .stat-pill {
        flex: 1;
        background: var(--beige);
        border-radius: 14px;
        padding: 1.2rem 1rem;
        text-align: center;
        box-shadow: 0 3px 12px var(--warm-shadow);
    }

    .stat-number {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: var(--brown);
        font-weight: 700;
        display: block;
    }

    .stat-label {
        font-size: .8rem;
        color: #9a6535;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    /* ═══════════════════════════════════════════════════════════════════════════
       HOW TO ORDER
    ═══════════════════════════════════════════════════════════════════════════ */
    #como-pedir {
        background: linear-gradient(135deg, #3D1C02 0%, #5c2a06 50%, #8B4513 100%);
        position: relative;
        overflow: hidden;
    }

    #como-pedir::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse at 10% 80%, rgba(212,164,100,0.12) 0%, transparent 50%),
            radial-gradient(ellipse at 90% 20%, rgba(255,248,240,0.05) 0%, transparent 45%);
        pointer-events: none;
    }

    #como-pedir .section-label { color: var(--caramel); }
    #como-pedir .section-title { color: var(--cream); }
    #como-pedir .section-divider { background: var(--caramel); }

    .steps-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        position: relative;
        z-index: 1;
    }

    .step-card {
        background: rgba(255,248,240,0.07);
        border: 1px solid rgba(212,164,100,0.2);
        border-radius: 18px;
        padding: 2rem 1.5rem;
        text-align: center;
        backdrop-filter: blur(4px);
        transition: background .25s, transform .25s;
    }
    .step-card:hover {
        background: rgba(255,248,240,0.12);
        transform: translateY(-4px);
    }

    .step-number {
        font-family: 'Dancing Script', cursive;
        font-size: 3.5rem;
        color: var(--caramel);
        line-height: 1;
        margin-bottom: .5rem;
        display: block;
    }

    .step-icon {
        font-size: 2rem;
        margin-bottom: .8rem;
        display: block;
    }

    .step-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.05rem;
        color: var(--cream);
        margin-bottom: .5rem;
    }

    .step-desc {
        font-size: .88rem;
        color: rgba(245,230,211,0.75);
        line-height: 1.6;
    }

    .whatsapp-cta-wrap {
        text-align: center;
        margin-top: 3.5rem;
        position: relative;
        z-index: 1;
    }

    .btn-whatsapp {
        display: inline-flex;
        align-items: center;
        gap: .75rem;
        padding: 1.05rem 2.5rem;
        background: #25D366;
        color: #fff;
        border-radius: 50px;
        font-family: 'Lato', sans-serif;
        font-weight: 700;
        font-size: 1.05rem;
        letter-spacing: .04em;
        box-shadow: 0 6px 28px rgba(37,211,102,0.35);
        transition: transform .2s, box-shadow .2s, background .2s;
    }
    .btn-whatsapp:hover {
        background: #1ebe5d;
        transform: translateY(-3px);
        box-shadow: 0 10px 36px rgba(37,211,102,0.45);
    }
    .btn-whatsapp svg { flex-shrink: 0; }

    .whatsapp-note {
        display: block;
        color: rgba(245,230,211,0.5);
        font-size: .82rem;
        margin-top: 1rem;
    }

    /* ═══════════════════════════════════════════════════════════════════════════
       FOOTER
    ═══════════════════════════════════════════════════════════════════════════ */
    footer {
        background: var(--choco);
        color: var(--beige);
        padding: 4rem 2rem 2rem;
    }

    .footer-inner {
        max-width: 1100px;
        margin: 0 auto;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 3rem;
        padding-bottom: 2.5rem;
        border-bottom: 1px solid rgba(245,230,211,0.1);
        margin-bottom: 2rem;
    }

    .footer-brand .footer-logo {
        font-family: 'Dancing Script', cursive;
        font-size: 2.2rem;
        color: var(--caramel);
        margin-bottom: .6rem;
    }

    .footer-brand p {
        font-size: .9rem;
        color: rgba(245,230,211,0.65);
        max-width: 280px;
        line-height: 1.7;
    }

    .footer-col h4 {
        font-family: 'Playfair Display', serif;
        font-size: 1rem;
        color: var(--caramel);
        margin-bottom: 1rem;
        letter-spacing: .04em;
    }

    .footer-col ul { list-style: none; }
    .footer-col ul li {
        margin-bottom: .5rem;
        font-size: .88rem;
        color: rgba(245,230,211,0.6);
    }

    .footer-col ul li a {
        color: rgba(245,230,211,0.6);
        transition: color .2s;
    }
    .footer-col ul li a:hover { color: var(--caramel); }

    .footer-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        font-size: .82rem;
        color: rgba(245,230,211,0.35);
    }

    .footer-bottom a {
        color: rgba(245,230,211,0.45);
        transition: color .2s;
    }
    .footer-bottom a:hover { color: var(--caramel); }

    /* ═══════════════════════════════════════════════════════════════════════════
       FLOATING WHATSAPP BUTTON
    ═══════════════════════════════════════════════════════════════════════════ */
    .whatsapp-float {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 200;
        width: 58px; height: 58px;
        background: #25D366;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(37,211,102,0.4);
        transition: transform .2s, box-shadow .2s;
    }
    .whatsapp-float:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 30px rgba(37,211,102,0.5);
    }
    .whatsapp-float svg { color: #fff; }

    /* Pulse ring */
    .whatsapp-float::before {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        border: 2px solid rgba(37,211,102,0.4);
        animation: pulse-ring 2.5s ease-out infinite;
    }
    @keyframes pulse-ring {
        0%   { transform: scale(1);   opacity: .7; }
        100% { transform: scale(1.5); opacity: 0;  }
    }

    /* ═══════════════════════════════════════════════════════════════════════════
       RESPONSIVE
    ═══════════════════════════════════════════════════════════════════════════ */
    @media (max-width: 900px) {
        .about-grid   { grid-template-columns: 1fr; }
        .about-visual { display: none; }
        .footer-grid  { grid-template-columns: 1fr 1fr; }
        .footer-brand { grid-column: 1/-1; }
    }

    @media (max-width: 620px) {
        nav { padding: .75rem 1.2rem; }
        .nav-links { display: none; flex-direction: column; position: absolute; top: 100%; left: 0; right: 0; background: rgba(61,28,2,0.97); padding: 1.5rem 1.5rem; gap: 1.2rem; }
        .nav-links.open { display: flex; }
        .nav-toggle { display: flex; }

        section { padding: 4rem 1.2rem; }
        .footer-grid { grid-template-columns: 1fr; }
        .steps-grid  { grid-template-columns: 1fr; }
        .products-grid { grid-template-columns: 1fr; }
        .product-card:nth-child(odd),
        .product-card:nth-child(even) { transform: none; }

        .about-stat-row { flex-direction: column; }
    }

    /* ═══════════════════════════════════════════════════════════════════════════
       CART
    ═══════════════════════════════════════════════════════════════════════════ */

    /* Nav cart button */
    .nav-cart-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--cream);
        position: relative;
        padding: .3rem .4rem;
        display: flex;
        align-items: center;
        gap: .45rem;
        font-family: 'Lato', sans-serif;
        font-size: .85rem;
        font-weight: 700;
        letter-spacing: .04em;
        opacity: .85;
        transition: opacity .2s;
    }
    .nav-cart-btn:hover { opacity: 1; }

    .cart-badge {
        position: absolute;
        top: -4px; right: -6px;
        background: var(--caramel);
        color: var(--choco);
        font-size: .65rem;
        font-weight: 900;
        width: 18px; height: 18px;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
    .cart-badge.visible { display: flex; }

    /* Drawer overlay */
    .cart-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(20,8,2,0.55);
        z-index: 500;
        backdrop-filter: blur(2px);
    }
    .cart-overlay.open { display: block; }

    /* Drawer panel */
    .cart-drawer {
        position: fixed;
        top: 0; right: 0;
        width: min(420px, 100vw);
        height: 100dvh;
        background: var(--cream);
        z-index: 501;
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform .32s cubic-bezier(.4,0,.2,1);
        box-shadow: -6px 0 40px rgba(61,28,2,0.18);
    }
    .cart-drawer.open { transform: translateX(0); }

    .cart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.3rem 1.6rem;
        border-bottom: 1px solid rgba(139,69,19,0.12);
        background: var(--choco);
        color: var(--cream);
        flex-shrink: 0;
    }
    .cart-header h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.15rem;
    }
    .cart-close-btn {
        background: none;
        border: none;
        color: var(--cream);
        cursor: pointer;
        font-size: 1.6rem;
        line-height: 1;
        opacity: .75;
        transition: opacity .2s;
        padding: .2rem;
    }
    .cart-close-btn:hover { opacity: 1; }

    /* Items list */
    .cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 1.4rem;
    }

    .cart-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: #a07050;
    }
    .cart-empty svg { margin: 0 auto 1rem; opacity: .35; display: block; }
    .cart-empty p { font-style: italic; font-size: .95rem; }

    .cart-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: .9rem 0;
        border-bottom: 1px solid rgba(139,69,19,0.08);
    }
    .cart-item-info { flex: 1; }
    .cart-item-name {
        font-weight: 700;
        font-size: .93rem;
        color: var(--choco);
        margin-bottom: .15rem;
    }
    .cart-item-price { font-size: .82rem; color: var(--brown); }

    .cart-qty {
        display: flex;
        align-items: center;
        gap: .4rem;
    }
    .cart-qty-btn {
        width: 26px; height: 26px;
        border-radius: 50%;
        border: 1.5px solid rgba(139,69,19,0.25);
        background: none;
        color: var(--choco);
        font-size: 1rem;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s;
    }
    .cart-qty-btn:hover { background: var(--beige); }
    .cart-qty-num { min-width: 20px; text-align: center; font-weight: 700; font-size: .9rem; }

    /* Footer */
    .cart-footer {
        padding: 1.2rem 1.6rem 1.6rem;
        border-top: 1px solid rgba(139,69,19,0.12);
        background: var(--beige);
        flex-shrink: 0;
    }
    .cart-total-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 1.1rem;
        font-size: 1rem;
        color: var(--choco);
    }
    .cart-total-row strong { font-size: 1.25rem; }

    .btn-cart-wa {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .7rem;
        width: 100%;
        padding: .95rem;
        background: #25D366;
        color: #fff;
        border: none;
        border-radius: 50px;
        font-family: 'Lato', sans-serif;
        font-weight: 700;
        font-size: 1rem;
        letter-spacing: .04em;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(37,211,102,0.35);
        transition: background .2s, transform .15s;
    }
    .btn-cart-wa:hover { background: #1ebe5d; transform: translateY(-2px); }
    .btn-cart-wa:disabled { opacity: .5; cursor: default; transform: none; }

    .btn-cart-clear {
        display: block;
        width: 100%;
        margin-top: .65rem;
        background: none;
        border: none;
        font-family: 'Lato', sans-serif;
        font-size: .8rem;
        color: #a07050;
        cursor: pointer;
        text-align: center;
        text-decoration: underline;
        padding: .3rem;
    }

    /* Product card "Agregar" button */
    .btn-agregar {
        padding: .55rem 1.1rem;
        background: var(--brown);
        color: var(--cream);
        border: none;
        border-radius: 50px;
        font-family: 'Lato', sans-serif;
        font-size: .82rem;
        font-weight: 700;
        letter-spacing: .05em;
        cursor: pointer;
        transition: background .2s, transform .15s;
        white-space: nowrap;
    }
    .btn-agregar:hover { background: var(--choco); transform: scale(1.04); }
    .btn-agregar.added {
        background: #27ae60;
        animation: pop .25s ease;
    }
    @keyframes pop {
        0%   { transform: scale(1); }
        50%  { transform: scale(1.12); }
        100% { transform: scale(1); }
    }

    /* ═══════════════════════════════════════════════════════════════════════════
       CUSTOMER PHOTOS GALLERY
    ═══════════════════════════════════════════════════════════════════════════ */
    #fotos-clientes { background: var(--cream); }

    .gallery-grid {
        columns: 3;
        column-gap: 1.2rem;
    }
    @media (max-width: 900px) { .gallery-grid { columns: 2; } }
    @media (max-width: 500px) { .gallery-grid { columns: 1; } }

    .gallery-item {
        break-inside: avoid;
        margin-bottom: 1.2rem;
        border-radius: 14px;
        overflow: hidden;
        position: relative;
        cursor: zoom-in;
        box-shadow: 0 4px 18px var(--warm-shadow);
        transition: transform .3s, box-shadow .3s;
    }
    .gallery-item:hover { transform: translateY(-4px); box-shadow: 0 10px 30px var(--warm-shadow); }

    .gallery-item img {
        width: 100%;
        display: block;
        transition: transform .4s;
    }
    .gallery-item:hover img { transform: scale(1.04); }

    .gallery-caption {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        background: linear-gradient(transparent, rgba(61,28,2,0.78));
        color: #fff;
        font-size: .82rem;
        padding: 1.4rem .9rem .7rem;
        line-height: 1.4;
        opacity: 0;
        transition: opacity .3s;
    }
    .gallery-item:hover .gallery-caption { opacity: 1; }

    .gallery-empty {
        text-align: center;
        padding: 2.5rem;
        color: #a07050;
        font-style: italic;
    }

    /* ═══════════════════════════════════════════════════════════════════════════
       REVIEWS
    ═══════════════════════════════════════════════════════════════════════════ */
    #resenas { background: var(--beige); }

    .reviews-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3.5rem;
    }

    .review-card {
        background: #fff;
        border: 1px solid rgba(139,69,19,0.1);
        border-radius: 18px;
        padding: 1.6rem 1.8rem;
        box-shadow: 0 4px 20px var(--warm-shadow);
        display: flex;
        flex-direction: column;
        gap: .75rem;
    }

    .review-stars { color: #D4A464; font-size: 1.15rem; letter-spacing: .05em; }

    .review-text {
        font-size: .94rem;
        line-height: 1.65;
        color: #5a3010;
        flex: 1;
    }

    .review-author {
        font-weight: 700;
        font-size: .85rem;
        color: var(--brown);
        border-top: 1px solid rgba(139,69,19,0.08);
        padding-top: .65rem;
    }

    .no-reviews {
        text-align: center;
        padding: 2rem;
        color: #a07050;
        font-style: italic;
        grid-column: 1/-1;
    }

    /* ── Form ── */
    .review-form-wrap {
        background: #fff;
        border: 1px solid rgba(139,69,19,0.12);
        border-radius: 22px;
        padding: 2.4rem 2.8rem;
        max-width: 600px;
        margin: 0 auto;
        box-shadow: 0 6px 30px var(--warm-shadow);
    }

    .review-form-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.3rem;
        color: var(--choco);
        margin-bottom: 1.4rem;
        text-align: center;
    }

    .form-group { margin-bottom: 1.1rem; }

    .form-group label {
        display: block;
        font-size: .85rem;
        font-weight: 700;
        color: var(--brown);
        margin-bottom: .4rem;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: .7rem 1rem;
        border: 1.5px solid rgba(139,69,19,0.2);
        border-radius: 10px;
        font-family: 'Lato', sans-serif;
        font-size: .95rem;
        color: var(--choco);
        background: var(--cream);
        transition: border-color .2s;
        resize: vertical;
    }
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--caramel);
    }

    /* Star rating input */
    .star-rating { display: flex; gap: .3rem; flex-direction: row-reverse; justify-content: flex-end; }
    .star-rating input { display: none; }
    .star-rating label {
        font-size: 1.6rem;
        color: #ddd;
        cursor: pointer;
        transition: color .15s;
        display: block;
        margin: 0;
        padding: 0;
        text-transform: none;
        letter-spacing: 0;
        font-weight: 400;
    }
    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label { color: #D4A464; }

    .btn-review-submit {
        display: block;
        width: 100%;
        padding: .85rem;
        background: var(--brown);
        color: var(--cream);
        border: none;
        border-radius: 50px;
        font-family: 'Lato', sans-serif;
        font-weight: 700;
        font-size: 1rem;
        letter-spacing: .05em;
        cursor: pointer;
        transition: background .2s, transform .15s;
        margin-top: 1.2rem;
    }
    .btn-review-submit:hover { background: var(--choco); transform: translateY(-2px); }
    .btn-review-submit:disabled { opacity: .6; cursor: default; transform: none; }

    .review-feedback {
        text-align: center;
        margin-top: 1rem;
        font-size: .92rem;
        min-height: 1.2em;
    }
    .review-feedback.ok  { color: #27ae60; }
    .review-feedback.err { color: #c0392b; }

    /* ═══════════════════════════════════════════════════════════════════════════
       LIGHTBOX
    ═══════════════════════════════════════════════════════════════════════════ */
    .product-img-wrap img { cursor: zoom-in; }

    #lightbox {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 999;
        background: rgba(20,8,2,0.88);
        backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }
    #lightbox.open { display: flex; }

    #lightbox-img {
        max-width: min(92vw, 860px);
        max-height: 88vh;
        object-fit: contain;
        border-radius: 10px;
        box-shadow: 0 8px 60px rgba(0,0,0,0.7);
        animation: lb-in .22s ease;
    }
    @keyframes lb-in {
        from { opacity: 0; transform: scale(.93); }
        to   { opacity: 1; transform: scale(1); }
    }

    #lightbox-close {
        position: fixed;
        top: 1.1rem;
        right: 1.4rem;
        background: rgba(255,255,255,0.12);
        border: none;
        color: #fff;
        font-size: 2rem;
        line-height: 1;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .2s;
    }
    #lightbox-close:hover { background: rgba(255,255,255,0.25); }
    </style>
</head>
<body>

<!-- ════════════════════════════════════════════════════════════ NAVIGATION ══ -->
<nav id="main-nav">
    <a href="#hero" class="nav-logo">
        <img src="assets/logo.png" alt="Chocodine logo">
        Chocodine
    </a>
    <ul class="nav-links" id="nav-links">
        <li><a href="#productos">Budines</a></li>
        <li><a href="#nosotros">Nosotros</a></li>
        <li><a href="#como-pedir">Cómo pedir</a></li>
        <li><a href="#resenas">Reseñas</a></li>
    </ul>
    <div style="display:flex;align-items:center;gap:.6rem;">
        <button class="nav-cart-btn" id="cart-btn" aria-label="Ver carrito">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            Carrito
            <span class="cart-badge" id="cart-badge">0</span>
        </button>
        <button class="nav-toggle" id="nav-toggle" aria-label="Abrir menú">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- ════════════════════════════════════════════════════════════════ HERO ══ -->
<section id="hero">
    <div class="hero-content">
        <img src="assets/logo.png" alt="Chocodine" class="hero-logo-img">
        <p class="hero-eyebrow">Hecho con amor &amp; los mejores ingredientes</p>
        <h1 class="hero-logo">Chocodine</h1>
        <p class="hero-tagline">Budines artesanales</p>
        <a href="#productos" class="hero-cta">
            <!-- Fork & knife icon -->
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>
            Ver nuestros budines
        </a>
    </div>
    <div class="hero-scroll">
        <span>Scroll</span>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════ PRODUCTS GRID ══ -->
<section id="productos">
    <div class="section-inner">
        <span class="section-label">Hechos hoy, para vos</span>
        <h2 class="section-title">Nuestros Budines</h2>
        <div class="section-divider"></div>

        <div class="products-grid">
        <?php if (empty($products)): ?>
            <div class="empty-products">
                <p>¡Pronto tendremos deliciosas opciones para vos!</p>
                <p style="margin-top:.5rem;font-size:.9rem;color:#a07050;">Aún no hay productos disponibles. Volvé más tarde o escribinos por WhatsApp.</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
            <article class="product-card">
                <div class="product-img-wrap">
                    <?php if (!empty($product['image'])): ?>
                        <img
                            src="<?= $product['image'] ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>"
                            loading="lazy"
                        >
                        <button
                            class="btn-download-story"
                            data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                            data-desc="<?= htmlspecialchars($product['description'], ENT_QUOTES) ?>"
                            data-price="<?= (int)$product['price'] ?>"
                            data-unit="<?= htmlspecialchars($product['unit_label'] ?? 'unidad', ENT_QUOTES) ?>"
                            data-cat="<?= htmlspecialchars($product['category'], ENT_QUOTES) ?>"
                            title="Descargar para compartir"
                            aria-label="Descargar imagen para compartir"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            <span class="dl-tooltip">Guardar &amp; compartir</span>
                        </button>
                    <?php else: ?>
                        <div class="product-placeholder">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="3"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                            <span>Sin foto aún</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="product-body">
                    <span class="product-category cat-<?= htmlspecialchars($product['category']) ?>">
                        <?= htmlspecialchars($product['category']) ?>
                    </span>
                    <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="product-desc"><?= htmlspecialchars($product['description']) ?></p>
                    <div class="product-footer">
                        <div class="product-price">
                            $<?= number_format($product['price'], 0, ',', '.') ?>
                            <small>/ <?= htmlspecialchars($product['unit_label'] ?? 'unidad') ?></small>
                        </div>
                        <button
                            class="btn-agregar"
                            data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                            data-price="<?= (int)$product['price'] ?>"
                            data-unit="<?= htmlspecialchars($product['unit_label'] ?? 'unidad', ENT_QUOTES) ?>"
                        >+ Agregar</button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════ ABOUT ══ -->
<section id="nosotros">
    <div class="section-inner">
        <div class="about-grid">
            <div class="about-text">
                <span class="section-label">Nuestra historia</span>
                <h2 class="section-title">Hecho con amor,<br>desde el primer día</h2>
                <div class="section-divider"></div>

                <p>Chocodine nació en una cocina familiar con una receta simple: ingredientes de primera calidad y mucho amor en cada preparación. Cada budín sale del horno con la calidez de algo hecho para alguien especial.</p>

                <p>No somos una fábrica. Somos una pequeña pastelería artesanal donde cada detalle importa: la selección del cacao, la ralladura de limón fresco, la vainilla. Todo preparado con dedicación y cariño.</p>

                <ul class="about-values">
                    <li>
                        <span class="icon">📦</span>
                        <span><strong>Packaging cuidado</strong> — cada budín llega protegido y presentado con cariño</span>
                    </li>
                    <li>
                        <span class="icon">💚</span>
                        <span><strong>Producción pequeña</strong> — garantizamos frescura en cada pedido</span>
                    </li>
                    <li>
                        <span class="icon">🏠</span>
                        <span><strong>Delivery a domicilio</strong> — Los Telares y alrededores, Santiago del Estero</span>
                    </li>
                </ul>
            </div>

            <div class="about-visual">
                <div class="about-quote-card">
                    <blockquote>"Un budín casero tiene el poder de hacer sentir a alguien querido, aunque estés lejos."</blockquote>
                    <cite>— Equipo Chocodine</cite>
                </div>
                <div class="about-stat-row">
                    <div class="stat-pill">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Artesanal</span>
                    </div>
                    <div class="stat-pill">
                        <span class="stat-number">+500</span>
                        <span class="stat-label">Pedidos felices</span>
                    </div>
                    <div class="stat-pill">
                        <span class="stat-number">7</span>
                        <span class="stat-label">Días disponible</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ HOW TO ORDER ══ -->
<section id="como-pedir">
    <div class="section-inner">
        <span class="section-label">Fácil y rápido</span>
        <h2 class="section-title">¿Cómo hacer tu pedido?</h2>
        <div class="section-divider"></div>

        <div class="steps-grid">
            <div class="step-card">
                <span class="step-number">01</span>
                <span class="step-icon">👀</span>
                <h3 class="step-title">Elegí tu budín</h3>
                <p class="step-desc">Recorrés la carta y elegís el sabor que más te tiente. ¿Chocolate? ¿Limón fresco? La decisión es tuya.</p>
            </div>
            <div class="step-card">
                <span class="step-number">02</span>
                <span class="step-icon">💬</span>
                <h3 class="step-title">Escribinos por WhatsApp</h3>
                <p class="step-desc">Mandanos un mensaje con tu pedido, dirección y horario de entrega. Te confirmamos disponibilidad al toque.</p>
            </div>
            <div class="step-card">
                <span class="step-number">03</span>
                <span class="step-icon">🍰</span>
                <h3 class="step-title">Lo horneamos para vos</h3>
                <p class="step-desc">Tu budín se prepara fresco, el mismo día de la entrega. Sin stock prearmado, siempre recién hecho.</p>
            </div>
            <div class="step-card">
                <span class="step-number">04</span>
                <span class="step-icon">🏠</span>
                <h3 class="step-title">Entrega a domicilio</h3>
                <p class="step-desc">Te lo llevamos a tu puerta, listo para disfrutar o regalar. También podés pasar a buscarlo si preferís.</p>
            </div>
        </div>

        <div class="whatsapp-cta-wrap">
            <a
                href="https://wa.me/5493856412855?text=Hola!%20Quiero%20hacer%20un%20pedido%20en%20Chocodine%20%F0%9F%8D%AB"
                target="_blank"
                rel="noopener"
                class="btn-whatsapp"
            >
                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                Pedir por WhatsApp
            </a>
            <span class="whatsapp-note">Respondemos en minutos · Lunes a Domingo 9 a 20 hs</span>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════ FOTOS DE CLIENTES ══ -->
<?php if (!empty($customer_photos)): ?>
<section id="fotos-clientes">
    <div class="section-inner">
        <span class="section-label">Amor en cada entrega</span>
        <h2 class="section-title">Fotos de nuestros clientes</h2>
        <div class="section-divider"></div>

        <div class="gallery-grid">
            <?php foreach ($customer_photos as $ph): ?>
            <div class="gallery-item" onclick="lbOpen(this.querySelector('img').src)">
                <img src="<?= $ph['image'] ?>" alt="Foto de cliente Chocodine" loading="lazy">
                <?php if ($ph['caption']): ?>
                    <div class="gallery-caption"><?= htmlspecialchars($ph['caption']) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════ RESEÑAS ══ -->
<section id="resenas">
    <div class="section-inner">
        <span class="section-label">Lo que dicen nuestros clientes</span>
        <h2 class="section-title">Reseñas</h2>
        <div class="section-divider"></div>

        <div class="reviews-grid">
        <?php if (empty($reviews)): ?>
            <p class="no-reviews">Aún no hay reseñas. ¡Sé el primero en comentar!</p>
        <?php else: ?>
            <?php foreach ($reviews as $r): ?>
            <div class="review-card">
                <div class="review-stars"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></div>
                <p class="review-text">"<?= htmlspecialchars($r['comment']) ?>"</p>
                <span class="review-author">— <?= htmlspecialchars($r['name']) ?></span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>

        <!-- Formulario -->
        <div class="review-form-wrap">
            <p class="review-form-title">Dejá tu comentario</p>
            <form id="review-form" novalidate>
                <div class="form-group">
                    <label for="rv-name">Tu nombre</label>
                    <input type="text" id="rv-name" name="name" placeholder="Ej: María" maxlength="80" required>
                </div>
                <div class="form-group">
                    <label>Puntuación</label>
                    <div class="star-rating">
                        <input type="radio" id="s5" name="rating" value="5" checked><label for="s5">★</label>
                        <input type="radio" id="s4" name="rating" value="4"><label for="s4">★</label>
                        <input type="radio" id="s3" name="rating" value="3"><label for="s3">★</label>
                        <input type="radio" id="s2" name="rating" value="2"><label for="s2">★</label>
                        <input type="radio" id="s1" name="rating" value="1"><label for="s1">★</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="rv-comment">Tu comentario</label>
                    <textarea id="rv-comment" name="comment" rows="4" maxlength="500" placeholder="Contanos tu experiencia con Chocodine..." required></textarea>
                </div>
                <button type="submit" class="btn-review-submit">Enviar reseña</button>
                <p class="review-feedback" id="review-feedback"></p>
            </form>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════ FOOTER ══ -->
<footer>
    <div class="footer-inner">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo" style="display:flex;align-items:center;gap:.7rem;">
                    <img src="assets/logo.png" alt="Chocodine" style="height:52px;width:52px;object-fit:contain;border-radius:50%;border:2px solid rgba(212,164,100,0.3);">
                    Chocodine
                </div>
                <p>Budines artesanales elaborados con amor. Cada mordida cuenta una historia.</p>
            </div>
            <div class="footer-col">
                <h4>Navegación</h4>
                <ul>
                    <li><a href="#hero">Inicio</a></li>
                    <li><a href="#productos">Nuestros budines</a></li>
                    <li><a href="#nosotros">Sobre nosotros</a></li>
                    <li><a href="#como-pedir">Cómo pedir</a></li>
                    <li><a href="#resenas">Reseñas</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contacto</h4>
                <ul>
                    <li>📱 WhatsApp: +54 9 3856 41-2855</li>
                    <li>📧 agostinaritaluna@gmail.com</li>
                    <li>📍 Los Telares, Santiago del Estero</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> Chocodine. Hecho con 🍫 y amor.</span>
            <a href="admin/login.php">Acceso administrador</a>
        </div>
    </div>
</footer>

<!-- ════════════════════════════════════════════════ FLOATING WHATSAPP ══ -->
<a
    href="https://wa.me/5493856412855?text=Hola!%20Quiero%20hacer%20un%20pedido%20en%20Chocodine%20%F0%9F%8D%AB"
    class="whatsapp-float"
    target="_blank"
    rel="noopener"
    aria-label="Contactar por WhatsApp"
>
    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
</a>

<!-- ═══════════════════════════════════════════════════════ CART DRAWER ══ -->
<div class="cart-overlay" id="cart-overlay"></div>
<aside class="cart-drawer" id="cart-drawer" aria-label="Carrito de compras">
    <div class="cart-header">
        <h3>Tu pedido</h3>
        <button class="cart-close-btn" id="cart-close" aria-label="Cerrar carrito">&times;</button>
    </div>
    <div class="cart-items" id="cart-items">
        <!-- JS renders this -->
    </div>
    <div class="cart-footer">
        <div class="cart-total-row">
            <span>Total</span>
            <strong id="cart-total">$0</strong>
        </div>
        <button class="btn-cart-wa" id="cart-wa-btn" disabled>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
            Enviar pedido por WhatsApp
        </button>
        <button class="btn-cart-clear" id="cart-clear">Vaciar carrito</button>
    </div>
</aside>

<!-- ══════════════════════════════════════════════════════ LIGHTBOX MODAL ══ -->
<div id="lightbox" role="dialog" aria-modal="true" aria-label="Foto completa">
    <button id="lightbox-close" aria-label="Cerrar">&times;</button>
    <img id="lightbox-img" src="" alt="">
</div>

<script>
// Mobile nav toggle
document.getElementById('nav-toggle').addEventListener('click', function() {
    document.getElementById('nav-links').classList.toggle('open');
});
// Close on link click
document.querySelectorAll('.nav-links a').forEach(function(a) {
    a.addEventListener('click', function() {
        document.getElementById('nav-links').classList.remove('open');
    });
});

// ── Cart ─────────────────────────────────────────────────────────────────────
(function() {
    var WA_NUMBER = '5493856412855';
    var STORAGE_KEY = 'chocodine_cart';

    // State
    var cart = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');

    // DOM refs
    var drawer   = document.getElementById('cart-drawer');
    var overlay  = document.getElementById('cart-overlay');
    var itemsEl  = document.getElementById('cart-items');
    var totalEl  = document.getElementById('cart-total');
    var badge    = document.getElementById('cart-badge');
    var waBtn    = document.getElementById('cart-wa-btn');

    function save() { localStorage.setItem(STORAGE_KEY, JSON.stringify(cart)); }

    function totalItems() { return cart.reduce(function(s, i) { return s + i.qty; }, 0); }
    function totalPrice() { return cart.reduce(function(s, i) { return s + i.price * i.qty; }, 0); }

    function fmt(n) { return '$' + n.toLocaleString('es-AR'); }

    function render() {
        var count = totalItems();

        // Badge
        if (count > 0) {
            badge.textContent = count;
            badge.classList.add('visible');
        } else {
            badge.classList.remove('visible');
        }

        // Total
        totalEl.textContent = fmt(totalPrice());

        // WA button
        waBtn.disabled = count === 0;

        // Items
        if (cart.length === 0) {
            itemsEl.innerHTML =
                '<div class="cart-empty">' +
                '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>' +
                '<p>Tu carrito está vacío.<br>Agregá algún budín para empezar.</p>' +
                '</div>';
            return;
        }

        itemsEl.innerHTML = cart.map(function(item, idx) {
            return '<div class="cart-item">' +
                '<div class="cart-item-info">' +
                    '<div class="cart-item-name">' + item.name + '</div>' +
                    '<div class="cart-item-price">' + fmt(item.price) + ' / ' + (item.unit || 'unidad') + ' — ' + fmt(item.price * item.qty) + '</div>' +
                '</div>' +
                '<div class="cart-qty">' +
                    '<button class="cart-qty-btn" data-idx="' + idx + '" data-op="dec">−</button>' +
                    '<span class="cart-qty-num">' + item.qty + '</span>' +
                    '<button class="cart-qty-btn" data-idx="' + idx + '" data-op="inc">+</button>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function addItem(name, price, unit) {
        var idx = cart.findIndex(function(i) { return i.name === name; });
        if (idx >= 0) {
            cart[idx].qty++;
        } else {
            cart.push({ name: name, price: price, unit: unit || 'unidad', qty: 1 });
        }
        save(); render();
    }

    function openDrawer()  { drawer.classList.add('open'); overlay.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function closeDrawer() { drawer.classList.remove('open'); overlay.classList.remove('open'); document.body.style.overflow = ''; }

    // Delegated qty buttons
    itemsEl.addEventListener('click', function(e) {
        var btn = e.target.closest('.cart-qty-btn');
        if (!btn) return;
        var idx = parseInt(btn.dataset.idx);
        if (btn.dataset.op === 'inc') {
            cart[idx].qty++;
        } else {
            cart[idx].qty--;
            if (cart[idx].qty <= 0) cart.splice(idx, 1);
        }
        save(); render();
    });

    // "Agregar" buttons on product cards
    document.querySelectorAll('.btn-agregar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            addItem(this.dataset.name, parseInt(this.dataset.price), this.dataset.unit);
            this.textContent = '✓ Agregado';
            this.classList.add('added');
            var self = this;
            setTimeout(function() {
                self.textContent = '+ Agregar';
                self.classList.remove('added');
            }, 1200);
        });
    });

    // Open/close
    document.getElementById('cart-btn').addEventListener('click', openDrawer);
    document.getElementById('cart-close').addEventListener('click', closeDrawer);
    overlay.addEventListener('click', closeDrawer);
    document.getElementById('cart-clear').addEventListener('click', function() {
        if (cart.length && confirm('¿Vaciar el carrito?')) { cart = []; save(); render(); }
    });

    // WhatsApp
    waBtn.addEventListener('click', function() {
        if (!cart.length) return;
        var lines = cart.map(function(i) {
            var unit = i.unit && i.unit !== 'unidad' ? ' ' + i.unit : '';
            return '• ' + i.name + ' x' + i.qty + unit + ' — ' + fmt(i.price * i.qty);
        });
        var msg = 'Hola Chocodine! 🛒 Quiero hacer un pedido:\n\n' +
                  lines.join('\n') +
                  '\n\n*Total: ' + fmt(totalPrice()) + '*';
        window.open('https://wa.me/' + WA_NUMBER + '?text=' + encodeURIComponent(msg), '_blank', 'noopener');
    });

    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDrawer();
    });

    render();
})();

// Review form
(function() {
    var form = document.getElementById('review-form');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var fb  = document.getElementById('review-feedback');
        var btn = form.querySelector('.btn-review-submit');
        fb.className = 'review-feedback';
        fb.textContent = '';
        btn.disabled = true;
        btn.textContent = 'Enviando…';

        fetch('submit_review.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                fb.className = 'review-feedback ok';
                fb.textContent = '¡Gracias! Tu reseña fue enviada y será publicada pronto.';
                form.reset();
            } else {
                fb.className = 'review-feedback err';
                fb.textContent = data.error || 'Ocurrió un error. Intentá de nuevo.';
            }
        })
        .catch(function() {
            fb.className = 'review-feedback err';
            fb.textContent = 'Sin conexión. Intentá de nuevo.';
        })
        .finally(function() {
            btn.disabled = false;
            btn.textContent = 'Enviar reseña';
        });
    });
})();

// Lightbox
(function() {
    var lb    = document.getElementById('lightbox');
    var lbImg = document.getElementById('lightbox-img');

    function open(src, alt) {
        lbImg.src = src;
        lbImg.alt = alt || '';
        lb.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function close() {
        lb.classList.remove('open');
        document.body.style.overflow = '';
        lbImg.src = '';
    }

    // Exponer para uso externo (galería de clientes)
    window.lbOpen = function(src) { open(src, ''); };

    document.querySelectorAll('.product-img-wrap img').forEach(function(img) {
        img.addEventListener('click', function() { open(this.src, this.alt); });
    });

    document.getElementById('lightbox-close').addEventListener('click', close);
    lb.addEventListener('click', function(e) { if (e.target === lb) close(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') close(); });
})();

// ── Descargar Story (WhatsApp / Instagram) ───────────────────────────────────
(function() {
    var W = 1080, H = 1920;

    function roundRect(ctx, x, y, w, h, r) {
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + w - r, y);
        ctx.quadraticCurveTo(x + w, y, x + w, y + r);
        ctx.lineTo(x + w, y + h - r);
        ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
        ctx.lineTo(x + r, y + h);
        ctx.quadraticCurveTo(x, y + h, x, y + h - r);
        ctx.lineTo(x, y + r);
        ctx.quadraticCurveTo(x, y, x + r, y);
        ctx.closePath();
    }

    function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
        var words = text.split(' ');
        var line = '';
        var lines = [];
        for (var n = 0; n < words.length; n++) {
            var testLine = line + words[n] + ' ';
            if (ctx.measureText(testLine).width > maxWidth && n > 0) {
                lines.push(line.trim());
                line = words[n] + ' ';
            } else {
                line = testLine;
            }
        }
        if (line.trim()) lines.push(line.trim());
        lines.forEach(function(l, i) { ctx.fillText(l, x, y + i * lineHeight); });
        return lines.length;
    }

    function drawStory(canvas, name, desc, price, unit, category, img) {
        var ctx = canvas.getContext('2d');
        canvas.width  = W;
        canvas.height = H;

        // ── Fondo ──────────────────────────────────────────────────────────────
        var bg = ctx.createLinearGradient(0, 0, 0, H);
        bg.addColorStop(0,   '#130600');
        bg.addColorStop(0.5, '#1e0a00');
        bg.addColorStop(1,   '#0a0300');
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, W, H);

        // Líneas de textura sutil
        ctx.strokeStyle = 'rgba(212,164,100,0.06)';
        ctx.lineWidth = 1;
        for (var i = 0; i < W; i += 80) {
            ctx.beginPath(); ctx.moveTo(i, 0); ctx.lineTo(i, H); ctx.stroke();
        }

        // ── Imagen del producto ────────────────────────────────────────────────
        var IMG_H = Math.round(H * 0.63);
        if (img) {
            var scale = Math.max(W / img.naturalWidth, IMG_H / img.naturalHeight);
            var sw = img.naturalWidth  * scale;
            var sh = img.naturalHeight * scale;
            ctx.save();
            ctx.rect(0, 0, W, IMG_H);
            ctx.clip();
            ctx.drawImage(img, (W - sw) / 2, 0, sw, sh);
            ctx.restore();
        } else {
            var ph = ctx.createLinearGradient(0, 0, W, IMG_H);
            ph.addColorStop(0, '#3D1C02');
            ph.addColorStop(1, '#5a2d0a');
            ctx.fillStyle = ph;
            ctx.fillRect(0, 0, W, IMG_H);
        }

        // Degradado superior (para legibilidad del branding)
        var topFade = ctx.createLinearGradient(0, 0, 0, 240);
        topFade.addColorStop(0, 'rgba(10,3,0,0.80)');
        topFade.addColorStop(1, 'rgba(10,3,0,0)');
        ctx.fillStyle = topFade;
        ctx.fillRect(0, 0, W, 240);

        // Degradado inferior de la imagen
        var botFade = ctx.createLinearGradient(0, IMG_H - 380, 0, IMG_H);
        botFade.addColorStop(0, 'rgba(19,6,0,0)');
        botFade.addColorStop(1, 'rgba(19,6,0,1)');
        ctx.fillStyle = botFade;
        ctx.fillRect(0, IMG_H - 380, W, 380);

        // ── Branding superior ─────────────────────────────────────────────────
        ctx.textAlign   = 'center';
        ctx.shadowColor = 'rgba(0,0,0,0.9)';
        ctx.shadowBlur  = 24;

        ctx.font      = '700 72px "Dancing Script", cursive';
        ctx.fillStyle = '#F5E6D3';
        ctx.fillText('Chocodine', W / 2, 108);

        ctx.font      = '400 30px "Lato", sans-serif';
        ctx.fillStyle = '#D4A464';
        ctx.letterSpacing = '0.12em';
        ctx.fillText('BUDINES ARTESANALES', W / 2, 158);
        ctx.letterSpacing = '0';

        ctx.shadowBlur = 0;

        // ── Área de contenido ─────────────────────────────────────────────────
        var cY = IMG_H + 55;

        // Badge categoría
        var catColors = {
            'Chocolate': { bg: '#3D1C02', txt: '#F5E6D3' },
            'Vainilla':  { bg: '#D4A464', txt: '#3D1C02' },
            'Frutas':    { bg: '#c2775a', txt: '#fff8f0' },
            'Especial':  { bg: '#8B4513', txt: '#FFF8F0' }
        };
        var cc = catColors[category] || { bg: '#3D1C02', txt: '#F5E6D3' };
        var bW = 280, bH = 58;
        roundRect(ctx, (W - bW) / 2, cY, bW, bH, 29);
        ctx.fillStyle = cc.bg;
        ctx.fill();
        ctx.font      = '700 26px "Lato", sans-serif';
        ctx.fillStyle = cc.txt;
        ctx.fillText(category.toUpperCase(), W / 2, cY + 39);
        cY += 100;

        // Nombre del producto
        ctx.shadowColor = 'rgba(0,0,0,0.6)';
        ctx.shadowBlur  = 12;
        ctx.font        = '700 88px "Playfair Display", serif';
        ctx.fillStyle   = '#F5E6D3';
        var nameLines   = wrapText(ctx, name, W / 2, cY, W - 140, 100);
        cY += nameLines * 100 + 44;
        ctx.shadowBlur  = 0;

        // Ornamento divisor
        var ox = W / 2;
        ctx.strokeStyle = '#D4A464';
        ctx.lineWidth   = 2.5;
        ctx.beginPath(); ctx.moveTo(ox - 120, cY); ctx.lineTo(ox - 24, cY); ctx.stroke();
        ctx.beginPath(); ctx.moveTo(ox + 24,  cY); ctx.lineTo(ox + 120, cY); ctx.stroke();
        ctx.save();
        ctx.translate(ox, cY);
        ctx.rotate(Math.PI / 4);
        ctx.fillStyle = '#D4A464';
        ctx.fillRect(-9, -9, 18, 18);
        ctx.restore();
        cY += 58;

        // Precio
        ctx.shadowColor = 'rgba(0,0,0,0.5)';
        ctx.shadowBlur  = 18;
        ctx.font        = '700 118px "Playfair Display", serif';
        ctx.fillStyle   = '#D4A464';
        ctx.fillText('$' + Number(price).toLocaleString('es-AR'), W / 2, cY + 110);
        ctx.shadowBlur  = 0;

        ctx.font      = '300 36px "Lato", sans-serif';
        ctx.fillStyle = '#a07050';
        ctx.fillText('por ' + unit, W / 2, cY + 165);
        cY += 240;

        // Descripción
        ctx.font      = '400 40px "Lato", sans-serif';
        ctx.fillStyle = '#c8a882';
        wrapText(ctx, desc, W / 2, cY, W - 200, 60);

        // ── Footer ────────────────────────────────────────────────────────────
        ctx.font      = '400 28px "Lato", sans-serif';
        ctx.fillStyle = 'rgba(212,164,100,0.40)';
        ctx.fillText('Pedidos por WhatsApp', W / 2, H - 68);
        ctx.fillStyle = 'rgba(212,164,100,0.25)';
        ctx.fillText('Los Telares, Santiago del Estero', W / 2, H - 32);
    }

    function downloadStory(name, desc, price, unit, category, imageSrc) {
        var canvas = document.createElement('canvas');

        function render(img) {
            drawStory(canvas, name, desc, price, unit, category, img);
            var link = document.createElement('a');
            link.download = name.toLowerCase().replace(/\s+/g, '_') + '_chocodine.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }

        if (imageSrc) {
            var img = new Image();
            img.onload  = function() { render(img); };
            img.onerror = function() { render(null); };
            // Si es base64 no hay problema de CORS
            img.src = imageSrc;
        } else {
            render(null);
        }
    }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-download-story');
        if (!btn) return;
        e.stopPropagation();
        e.preventDefault();

        var card     = btn.closest('.product-card');
        var imgEl    = card.querySelector('.product-img-wrap img');

        downloadStory(
            btn.dataset.name,
            btn.dataset.desc,
            btn.dataset.price,
            btn.dataset.unit,
            btn.dataset.cat,
            imgEl ? imgEl.src : null
        );
    });
})();
</script>
</body>
</html>
