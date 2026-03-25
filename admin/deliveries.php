<?php
$pageTitle = 'Livraisons';
require_once __DIR__ . '/includes_top.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_delivery'])) {
    $deliveryId = (int) (isset($_POST['delivery_id']) ? $_POST['delivery_id'] : 0);
    $status = trim(isset($_POST['status']) ? $_POST['status'] : 'en attente');
    $trackingNumber = trim(isset($_POST['tracking_number']) ? $_POST['tracking_number'] : '');

    $deliveryStatement = $pdo->prepare('UPDATE deliveries SET status = ?, tracking_number = ? WHERE id = ?');
    $deliveryStatement->execute([$status, $trackingNumber, $deliveryId]);

    $orderStatement = $pdo->prepare('UPDATE orders o JOIN deliveries d ON d.order_id = o.id SET o.delivery_status = d.status WHERE d.id = ?');
    $orderStatement->execute([$deliveryId]);

    flash('success', 'Livraison mise à jour.');
    redirect('admin/deliveries.php');
}

$deliveries = $pdo->query(
    'SELECT d.*, o.order_number
     FROM deliveries d
     JOIN orders o ON o.id = d.order_id
     ORDER BY d.created_at DESC'
)->fetchAll();
?>

<div class="glass-panel">
    <h3 class="h4 mb-4">Gestion des livraisons</h3>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Méthode</th>
                    <th>Zone / Ville</th>
                    <th>Frais</th>
                    <th>Statut</th>
                    <th>Tracking</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deliveries as $delivery): ?>
                    <tr>
                        <td><?= e($delivery['order_number']) ?></td>
                        <td><?= e($delivery['delivery_method']) ?></td>
                        <td>
                            <div><?= e($delivery['delivery_zone']) ?></div>
                            <div class="small-muted"><?= e($delivery['delivery_city']) ?></div>
                        </td>
                        <td><?= format_price((float) $delivery['fee']) ?></td>
                        <td><span class="badge text-bg-<?= badge_class($delivery['status']) ?>"><?= e($delivery['status']) ?></span></td>
                        <td><?= e($delivery['tracking_number']) ?></td>
                        <td>
                            <form method="post" class="d-grid gap-2">
                                <input type="hidden" name="delivery_id" value="<?= (int) $delivery['id'] ?>">
                                <select name="status" class="form-select form-select-sm">
                                    <?php foreach (['en attente', 'en préparation', 'expédiée', 'livrée'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= $delivery['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="tracking_number" class="form-control form-control-sm" value="<?= e($delivery['tracking_number']) ?>" placeholder="Référence tracking">
                                <button class="btn btn-dark btn-sm" type="submit" name="update_delivery" value="1">Mettre à jour</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes_bottom.php'; ?>
