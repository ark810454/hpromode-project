<?php
$pageTitle = 'Paiements';
require_once __DIR__ . '/includes_top.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    $paymentId = (int) (isset($_POST['payment_id']) ? $_POST['payment_id'] : 0);
    $status = trim(isset($_POST['status']) ? $_POST['status'] : 'en attente');
    $note = trim(isset($_POST['payment_note']) ? $_POST['payment_note'] : '');

    $paymentStatement = $pdo->prepare('UPDATE payments SET status = ?, payment_note = ? WHERE id = ?');
    $paymentStatement->execute([$status, $note, $paymentId]);

    $orderStatement = $pdo->prepare('UPDATE orders o JOIN payments p ON p.order_id = o.id SET o.payment_status = p.status WHERE p.id = ?');
    $orderStatement->execute([$paymentId]);

    flash('success', 'Paiement mis à jour.');
    redirect('admin/payments.php');
}

$payments = $pdo->query(
    'SELECT p.*, o.order_number
     FROM payments p
     JOIN orders o ON o.id = p.order_id
     ORDER BY p.created_at DESC'
)->fetchAll();
?>

<div class="glass-panel">
    <h3 class="h4 mb-4">Gestion des paiements</h3>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Méthode</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Référence</th>
                    <th>Note</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= e($payment['order_number']) ?></td>
                        <td><?= e($payment['payment_method']) ?></td>
                        <td><?= format_price((float) $payment['amount']) ?></td>
                        <td><span class="badge text-bg-<?= badge_class($payment['status']) ?>"><?= e($payment['status']) ?></span></td>
                        <td><?= e($payment['transaction_reference']) ?></td>
                        <td><?= e($payment['payment_note']) ?></td>
                        <td>
                            <form method="post" class="d-grid gap-2">
                                <input type="hidden" name="payment_id" value="<?= (int) $payment['id'] ?>">
                                <select name="status" class="form-select form-select-sm">
                                    <?php foreach (['en attente', 'validé', 'échoué'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= $payment['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="payment_note" class="form-control form-control-sm" value="<?= e($payment['payment_note']) ?>" placeholder="Note admin">
                                <button class="btn btn-dark btn-sm" type="submit" name="update_payment" value="1">Mettre à jour</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes_bottom.php'; ?>
