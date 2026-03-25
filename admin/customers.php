<?php
$pageTitle = 'Clients';
require_once __DIR__ . '/includes_top.php';

$customers = $pdo->query(
    'SELECT u.*, COUNT(o.id) AS order_count, COALESCE(SUM(o.total_amount), 0) AS total_spent
     FROM users u
     LEFT JOIN orders o ON o.user_id = u.id
     GROUP BY u.id
     ORDER BY u.created_at DESC'
)->fetchAll();
?>

<div class="glass-panel">
    <h3 class="h4 mb-4">Liste des clients</h3>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Contact</th>
                    <th>Commandes</th>
                    <th>Total dépensé</th>
                    <th>Inscription</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td>
                            <strong><?= e($customer['first_name'] . ' ' . $customer['last_name']) ?></strong>
                            <div class="small-muted"><?= e($customer['country'] ?: 'Pays non renseigné') ?></div>
                        </td>
                        <td>
                            <div><?= e($customer['email']) ?></div>
                            <div class="small-muted"><?= e($customer['phone'] ?: 'Téléphone non renseigné') ?></div>
                        </td>
                        <td><?= (int) $customer['order_count'] ?></td>
                        <td><?= format_price((float) $customer['total_spent']) ?></td>
                        <td><?= e(date('d/m/Y', strtotime($customer['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes_bottom.php'; ?>
