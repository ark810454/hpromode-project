<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$orderId = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
$userId = (int) current_user()['id'];

$orderStatement = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
$orderStatement->execute([$orderId, $userId]);
$order = $orderStatement->fetch();

if (!$order) {
    flash('danger', 'Commande introuvable.');
    redirect('profile.php');
}

$itemsStatement = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC');
$itemsStatement->execute([$orderId]);
$orderItems = $itemsStatement->fetchAll();

$paymentStatement = $pdo->prepare('SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1');
$paymentStatement->execute([$orderId]);
$payment = $paymentStatement->fetch();

$deliveryStatement = $pdo->prepare('SELECT * FROM deliveries WHERE order_id = ? ORDER BY id DESC LIMIT 1');
$deliveryStatement->execute([$orderId]);
$delivery = $deliveryStatement->fetch();

$pageTitle = 'Détail de commande';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-banner compact-banner">
    <div class="container">
        <p class="eyebrow">Commande</p>
        <h1 class="section-title text-white"><?= e($order['order_number']) ?></h1>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-panel">
                    <h2 class="h4 mb-4">Articles commandés</h2>
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
                                <?php foreach ($orderItems as $item): ?>
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
            <div class="col-lg-4">
                <div class="summary-card mb-4">
                    <h2 class="h4 mb-3">Résumé</h2>
                    <div class="summary-line"><span>Sous-total</span><strong><?= format_price((float) $order['subtotal']) ?></strong></div>
                    <div class="summary-line"><span>Remise</span><strong>- <?= format_price((float) $order['discount_amount']) ?></strong></div>
                    <div class="summary-line"><span>Livraison</span><strong><?= format_price((float) $order['delivery_fee']) ?></strong></div>
                    <div class="summary-line total-line"><span>Total final</span><strong><?= format_price((float) $order['total_amount']) ?></strong></div>
                </div>

                <div class="glass-panel mb-4">
                    <h2 class="h4 mb-3">Paiement</h2>
                    <p class="mb-2"><strong>Méthode :</strong> <?= e($order['payment_method']) ?></p>
                    <p class="mb-2"><strong>Statut :</strong> <span class="badge text-bg-<?= badge_class($order['payment_status']) ?>"><?= e($order['payment_status']) ?></span></p>
                    <?php if ($payment): ?>
                        <p class="mb-2"><strong>Référence :</strong> <?= e($payment['transaction_reference']) ?></p>
                        <p class="mb-0 small-muted"><?= e($payment['payment_note']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="glass-panel">
                    <h2 class="h4 mb-3">Livraison</h2>
                    <p class="mb-2"><strong>Méthode :</strong> <?= e($order['delivery_method']) ?></p>
                    <p class="mb-2"><strong>Zone :</strong> <?= e($order['delivery_zone']) ?></p>
                    <p class="mb-2"><strong>Statut :</strong> <span class="badge text-bg-<?= badge_class($order['delivery_status']) ?>"><?= e($order['delivery_status']) ?></span></p>
                    <?php if ($delivery): ?>
                        <p class="mb-0"><strong>Suivi :</strong> <?= e($delivery['tracking_number']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
