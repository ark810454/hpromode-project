<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$adminPage = basename($_SERVER['PHP_SELF']);
$adminStyleVersion = file_exists(ROOT_PATH . '/assets/css/style.css') ? filemtime(ROOT_PATH . '/assets/css/style.css') : time();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(isset($pageTitle) ? $pageTitle : 'Administration') ?> | <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('css/style.css?v=' . $adminStyleVersion)) ?>">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <p class="eyebrow text-gold mb-1">Administration</p>
                <h1 class="brand-mark mb-2">HPROMODE</h1>
                <p class="small text-white-50 mb-0">Gestion simple du catalogue, des commandes et de la marque.</p>
            </div>

            <nav class="admin-nav">
                <a class="<?= $adminPage === 'index.php' ? 'active' : '' ?>" href="<?= e(base_url('admin/index.php')) ?>">Dashboard</a>
                <a class="<?= $adminPage === 'products.php' ? 'active' : '' ?>" href="<?= e(base_url('admin/products.php')) ?>">Articles</a>
                <a class="<?= $adminPage === 'categories.php' ? 'active' : '' ?>" href="<?= e(base_url('admin/categories.php')) ?>">Categories</a>
                <a class="<?= $adminPage === 'customers.php' ? 'active' : '' ?>" href="<?= e(base_url('admin/customers.php')) ?>">Clients</a>
                <a class="<?= $adminPage === 'orders.php' || $adminPage === 'order-details.php' ? 'active' : '' ?>" href="<?= e(base_url('admin/orders.php')) ?>">Commandes</a>
                <a class="<?= $adminPage === 'payments.php' ? 'active' : '' ?>" href="<?= e(base_url('admin/payments.php')) ?>">Paiements</a>
                <a class="<?= $adminPage === 'promotions.php' ? 'active' : '' ?>" href="<?= e(base_url('admin/promotions.php')) ?>">Promotions</a>
                <a class="<?= $adminPage === 'deliveries.php' ? 'active' : '' ?>" href="<?= e(base_url('admin/deliveries.php')) ?>">Livraisons</a>
                <a href="<?= e(base_url('index.php')) ?>">Voir le site</a>
                <a href="<?= e(base_url('admin/logout.php')) ?>">Deconnexion</a>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-topbar">
                <div>
                    <p class="eyebrow mb-1">Back-office</p>
                    <h2 class="mb-0"><?= e(isset($pageTitle) ? $pageTitle : 'Administration') ?></h2>
                </div>
                <div class="small-muted">
                    Connecte en tant que <?= e(array_value(current_admin(), 'name', 'Administrateur')) ?>
                </div>
            </div>

            <?php if ($message = flash('success')): ?>
                <div class="alert alert-success luxury-alert"><?= e($message) ?></div>
            <?php endif; ?>
            <?php if ($message = flash('danger')): ?>
                <div class="alert alert-danger luxury-alert"><?= e($message) ?></div>
            <?php endif; ?>
