<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

$pageTitle = isset($pageTitle) ? $pageTitle : APP_NAME;
$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$parsedPath = parse_url($requestUri, PHP_URL_PATH);
$currentPath = basename($parsedPath ? $parsedPath : '');
$isHomePage = $currentPath === 'index.php' || $currentPath === '';
$bodyClass = $isHomePage ? 'page-home' : 'page-inner';
$styleVersion = file_exists(ROOT_PATH . '/assets/css/style.css') ? filemtime(ROOT_PATH . '/assets/css/style.css') : time();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('css/style.css?v=' . $styleVersion)) ?>">
</head>
<body class="<?= e($bodyClass) ?>">
<div class="site-shell">
    <nav class="navbar navbar-expand-xl luxury-nav" data-site-nav>
        <div class="container luxury-nav-shell">
            <button class="navbar-toggler border-0 shadow-none order-1" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-label="Ouvrir le menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand brand-mark order-2 order-xl-2 mx-xl-auto" href="<?= e(base_url('index.php')) ?>">
                HPROMODE
            </a>

            <div class="header-actions order-3">
                <a class="header-utility d-none d-lg-inline-flex" href="<?= e(base_url('shop.php')) ?>" aria-label="Recherche">
                    <span class="header-utility-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="11" cy="11" r="6"></circle>
                            <path d="M16 16l5 5"></path>
                        </svg>
                    </span>
                    <span>Recherche</span>
                </a>
                <a class="header-utility" href="<?= e(base_url('cart.php')) ?>" aria-label="Panier">
                    <span class="header-utility-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 5h2l2.2 9.2a1 1 0 0 0 1 .8H18a1 1 0 0 0 1-.8L21 7H7"></path>
                            <circle cx="10" cy="19" r="1.3"></circle>
                            <circle cx="18" cy="19" r="1.3"></circle>
                        </svg>
                    </span>
                    <span class="d-none d-md-inline">Panier</span>
                    <span class="utility-badge"><?= cart_items_count() ?></span>
                </a>
                <?php if (is_logged_in()): ?>
                    <a class="header-utility" href="<?= e(base_url('profile.php')) ?>" aria-label="Compte">
                        <span class="header-utility-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="8" r="4"></circle>
                                <path d="M4 20c1.8-3.6 5-5.4 8-5.4s6.2 1.8 8 5.4"></path>
                            </svg>
                        </span>
                        <span class="d-none d-md-inline">Compte</span>
                    </a>
                <?php else: ?>
                    <a class="header-utility" href="<?= e(base_url('login.php')) ?>" aria-label="Connexion">
                        <span class="header-utility-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="8" r="4"></circle>
                                <path d="M4 20c1.8-3.6 5-5.4 8-5.4s6.2 1.8 8 5.4"></path>
                            </svg>
                        </span>
                        <span class="d-none d-md-inline">Connexion</span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="collapse navbar-collapse order-4 order-xl-1" id="navMenu">
                <ul class="navbar-nav nav-primary me-auto">
                    <li class="nav-item"><a class="nav-link <?= $currentPath === 'index.php' ? 'active' : '' ?>" href="<?= e(base_url('index.php')) ?>">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link <?= $currentPath === 'shop.php' && !isset($_GET['category']) ? 'active' : '' ?>" href="<?= e(base_url('shop.php')) ?>">Boutique</a></li>
                    <li class="nav-item"><a class="nav-link <?= isset($_GET['category']) && $_GET['category'] === 'robes' ? 'active' : '' ?>" href="<?= e(base_url('shop.php?category=robes')) ?>">Robes</a></li>
                    <li class="nav-item"><a class="nav-link <?= isset($_GET['category']) && $_GET['category'] === 'costumes' ? 'active' : '' ?>" href="<?= e(base_url('shop.php?category=costumes')) ?>">Costumes</a></li>
                    <li class="nav-item"><a class="nav-link <?= isset($_GET['category']) && $_GET['category'] === 'sacs' ? 'active' : '' ?>" href="<?= e(base_url('shop.php?category=sacs')) ?>">Sacs</a></li>
                    <li class="nav-item"><a class="nav-link <?= isset($_GET['category']) && $_GET['category'] === 'bijoux' ? 'active' : '' ?>" href="<?= e(base_url('shop.php?category=bijoux')) ?>">Bijoux</a></li>
                </ul>
                <div class="nav-secondary d-xl-none">
                    <?php if (is_logged_in()): ?>
                        <a href="<?= e(base_url('profile.php')) ?>">Mon compte</a>
                        <a href="<?= e(base_url('logout.php')) ?>">Deconnexion</a>
                    <?php else: ?>
                        <a href="<?= e(base_url('login.php')) ?>">Connexion</a>
                        <a href="<?= e(base_url('register.php')) ?>">Creer un compte</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <div class="flash-shell container">
            <?php if ($message = flash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show luxury-alert" role="alert">
                    <?= e($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            <?php endif; ?>
            <?php if ($message = flash('danger')): ?>
                <div class="alert alert-danger alert-dismissible fade show luxury-alert" role="alert">
                    <?= e($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            <?php endif; ?>
        </div>
