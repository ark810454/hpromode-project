<?php
$pageTitle = 'Détail commande';
require_once __DIR__ . '/includes_top.php';

$orderId = (int) (isset($_GET['id']) ? $_GET['id'] : 0);

$orderStatement = $pdo->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
$orderStatement->execute([$orderId]);
$order = $orderStatement->fetch();

if (!$order) {
    flash('danger', 'Commande introuvable.');
    redirect('admin/orders.php');
}

$itemsStatement = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC');
$itemsStatement->execute([$orderId]);
$items = $itemsStatement->fetchAll();

$paymentStatement = $pdo->prepare('SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1');
$paymentStatement->execute([$orderId]);
$payment = $paymentStatement->fetch();

$deliveryStatement = $pdo->prepare('SELECT * FROM deliveries WHERE order_id = ? ORDER BY id DESC LIMIT 1');
$deliveryStatement->execute([$orderId]);
$delivery = $deliveryStatement->fetch();
?>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="glass-panel">
            <h3 class="h4 mb-4">Articles de la commande <?= e($order['order_number']) ?></h3>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Options</th>
                            <th>Quantité</th>
                            <th>Prix</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= e($item['product_name']) ?></td>
                                <td>
                                    <div class="small-muted">Taille : <?= e($item['size'] ?: 'Unique') ?></div>
                                    <div class="small-muted">Couleur : <?= e($item['color'] ?: 'Unique') ?></div>
                                </td>
                                <td><?= (int) $item['quantity'] ?></td>
                                <td><?= format_price((float) $item['unit_price']) ?></td>
                                <td><?= format_price((float) $item['line_total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="summary-card mb-4">
            <h3 class="h4 mb-3">Résumé</h3>
            <div class="summary-line"><span>Sous-total</span><strong><?= format_price((float) $order['subtotal']) ?></strong></div>
            <div class="summary-line"><span>Remise</span><strong>- <?= format_price((float) $order['discount_amount']) ?></strong></div>
            <div class="summary-line"><span>Livraison</span><strong><?= format_price((float) $order['delivery_fee']) ?></strong></div>
            <div class="summary-line total-line"><span>Total final</span><strong><?= format_price((float) $order['total_amount']) ?></strong></div>
        </div>
        <div class="glass-panel mb-4">
            <h3 class="h4 mb-3">Paiement</h3>
            <p class="mb-2"><strong>Méthode :</strong> <?= e($order['payment_method']) ?></p>
            <p class="mb-2"><strong>Statut :</strong> <span class="badge text-bg-<?= badge_class($order['payment_status']) ?>"><?= e($order['payment_status']) ?></span></p>
            <?php if ($payment): ?>
                <p class="mb-2"><strong>Référence :</strong> <?= e($payment['transaction_reference']) ?></p>
                <p class="small-muted mb-0"><?= e($payment['payment_note']) ?></p>
            <?php endif; ?>
        </div>
        <div class="glass-panel">
            <h3 class="h4 mb-3">Livraison</h3>
            <p class="mb-2"><strong>Méthode :</strong> <?= e($order['delivery_method']) ?></p>
            <p class="mb-2"><strong>Zone :</strong> <?= e($order['delivery_zone']) ?></p>
            <p class="mb-2"><strong>Statut :</strong> <span class="badge text-bg-<?= badge_class($order['delivery_status']) ?>"><?= e($order['delivery_status']) ?></span></p>
            <?php if ($delivery): ?>
                <p class="mb-0"><strong>Tracking :</strong> <?= e($delivery['tracking_number']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes_bottom.php'; ?>
