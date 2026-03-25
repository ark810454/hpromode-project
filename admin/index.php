<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes_top.php';

$stats = array(
    'products' => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'customers' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'sales' => (float) $pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM orders')->fetchColumn(),
);

$recentOrders = $pdo->query(
    'SELECT id, order_number, first_name, last_name, total_amount, payment_status, delivery_status, created_at
     FROM orders ORDER BY created_at DESC LIMIT 6'
)->fetchAll();

$lowStockProducts = $pdo->query(
    'SELECT id, name, stock FROM products WHERE stock <= ' . LOW_STOCK_THRESHOLD . ' ORDER BY stock ASC, name ASC LIMIT 6'
)->fetchAll();

$recentProducts = $pdo->query(
    'SELECT id, name, sku, stock, created_at FROM products ORDER BY created_at DESC LIMIT 5'
)->fetchAll();
?>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="summary-card h-100">
            <span class="eyebrow">Articles</span>
            <strong class="display-6"><?= $stats['products'] ?></strong>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="summary-card h-100">
            <span class="eyebrow">Commandes</span>
            <strong class="display-6"><?= $stats['orders'] ?></strong>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="summary-card h-100">
            <span class="eyebrow">Clients</span>
            <strong class="display-6"><?= $stats['customers'] ?></strong>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="summary-card h-100">
            <span class="eyebrow">Ventes</span>
            <strong class="display-6"><?= format_price($stats['sales']) ?></strong>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="glass-panel h-100">
            <p class="eyebrow">Actions rapides</p>
            <h3 class="h4 mb-4">Gerer le catalogue</h3>
            <div class="d-grid gap-3">
                <a class="btn btn-dark" href="<?= e(base_url('admin/products.php')) ?>">Ajouter ou modifier un article</a>
                <a class="btn btn-outline-dark" href="<?= e(base_url('admin/categories.php')) ?>">Ajouter ou modifier une categorie</a>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="glass-panel h-100">
            <p class="eyebrow">Stock faible</p>
            <h3 class="h4 mb-3">Articles a surveiller</h3>
            <?php if ($lowStockProducts === array()): ?>
                <p class="small-muted mb-0">Aucune alerte stock pour le moment.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($lowStockProducts as $product): ?>
                        <li class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                            <span><?= e($product['name']) ?></span>
                            <strong><?= (int) $product['stock'] ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="glass-panel h-100">
            <p class="eyebrow">Derniers articles</p>
            <h3 class="h4 mb-3">Ajouts recents</h3>
            <?php if ($recentProducts === array()): ?>
                <p class="small-muted mb-0">Aucun article recemment ajoute.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($recentProducts as $product): ?>
                        <li class="list-group-item bg-transparent px-0">
                            <strong><?= e($product['name']) ?></strong>
                            <div class="small-muted"><?= e($product['sku']) ?> - Stock <?= (int) $product['stock'] ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="glass-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h4 mb-0">Commandes recentes</h3>
        <a class="btn btn-outline-dark btn-sm" href="<?= e(base_url('admin/orders.php')) ?>">Voir tout</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Client</th>
                    <th>Total</th>
                    <th>Paiement</th>
                    <th>Livraison</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><a href="<?= e(base_url('admin/order-details.php?id=' . (int) $order['id'])) ?>"><?= e($order['order_number']) ?></a></td>
                        <td><?= e($order['first_name'] . ' ' . $order['last_name']) ?></td>
                        <td><?= format_price((float) $order['total_amount']) ?></td>
                        <td><span class="badge text-bg-<?= badge_class($order['payment_status']) ?>"><?= e($order['payment_status']) ?></span></td>
                        <td><span class="badge text-bg-<?= badge_class($order['delivery_status']) ?>"><?= e($order['delivery_status']) ?></span></td>
                        <td><?= e(date('d/m/Y', strtotime($order['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes_bottom.php'; ?>
