<?php
$pageTitle = 'Commandes';
require_once __DIR__ . '/includes_top.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $orderId = (int) (isset($_POST['order_id']) ? $_POST['order_id'] : 0);
    $paymentStatus = trim(isset($_POST['payment_status']) ? $_POST['payment_status'] : 'en attente');
    $deliveryStatus = trim(isset($_POST['delivery_status']) ? $_POST['delivery_status'] : 'en attente');

    $orderStatement = $pdo->prepare('UPDATE orders SET payment_status = ?, delivery_status = ? WHERE id = ?');
    $orderStatement->execute([$paymentStatus, $deliveryStatus, $orderId]);

    $paymentStatement = $pdo->prepare('UPDATE payments SET status = ? WHERE order_id = ?');
    $paymentStatement->execute([$paymentStatus, $orderId]);

    $deliveryStatement = $pdo->prepare('UPDATE deliveries SET status = ? WHERE order_id = ?');
    $deliveryStatement->execute([$deliveryStatus, $orderId]);

    flash('success', 'Commande mise à jour.');
    redirect('admin/orders.php');
}

$orders = $pdo->query(
    'SELECT id, order_number, first_name, last_name, total_amount, payment_method, payment_status, delivery_status, created_at
     FROM orders
     ORDER BY created_at DESC'
)->fetchAll();
?>

<div class="glass-panel">
    <h3 class="h4 mb-4">Gestion des commandes</h3>
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
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><a href="<?= e(base_url('admin/order-details.php?id=' . (int) $order['id'])) ?>"><?= e($order['order_number']) ?></a></td>
                        <td><?= e($order['first_name'] . ' ' . $order['last_name']) ?></td>
                        <td><?= format_price((float) $order['total_amount']) ?></td>
                        <td>
                            <div><?= e($order['payment_method']) ?></div>
                            <span class="badge text-bg-<?= badge_class($order['payment_status']) ?>"><?= e($order['payment_status']) ?></span>
                        </td>
                        <td><span class="badge text-bg-<?= badge_class($order['delivery_status']) ?>"><?= e($order['delivery_status']) ?></span></td>
                        <td><?= e(date('d/m/Y', strtotime($order['created_at']))) ?></td>
                        <td>
                            <form method="post" class="d-grid gap-2">
                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                <select name="payment_status" class="form-select form-select-sm">
                                    <?php foreach (['en attente', 'validé', 'échoué'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= $order['payment_status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="delivery_status" class="form-select form-select-sm">
                                    <?php foreach (['en attente', 'en préparation', 'expédiée', 'livrée'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= $order['delivery_status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-dark btn-sm" type="submit" name="update_order" value="1">Mettre à jour</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes_bottom.php'; ?>
