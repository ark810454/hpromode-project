<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $firstName = trim(isset($_POST['first_name']) ? $_POST['first_name'] : '');
        $lastName = trim(isset($_POST['last_name']) ? $_POST['last_name'] : '');
        $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
        $phone = trim(isset($_POST['phone']) ? $_POST['phone'] : '');
        $address = trim(isset($_POST['address']) ? $_POST['address'] : '');
        $city = trim(isset($_POST['city']) ? $_POST['city'] : '');
        $country = trim(isset($_POST['country']) ? $_POST['country'] : '');

        if ($firstName === '' || $lastName === '' || $email === '') {
            flash('danger', 'Prénom, nom et email sont obligatoires.');
            redirect('profile.php');
        }

        $statement = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $statement->execute([$email, (int) $user['id']]);
        if ($statement->fetch()) {
            flash('danger', 'Cet email est déjà utilisé par un autre compte.');
            redirect('profile.php');
        }

        $update = $pdo->prepare(
            'UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, city = ?, country = ? WHERE id = ?'
        );
        $update->execute([$firstName, $lastName, $email, $phone, $address, $city, $country, (int) $user['id']]);

        sync_user_session($pdo, (int) $user['id']);
        flash('success', 'Profil mis à jour.');
        redirect('profile.php');
    }

    if (isset($_POST['update_password'])) {
        $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirmPassword = isset($_POST['confirm_new_password']) ? $_POST['confirm_new_password'] : '';

        $statement = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $statement->execute([(int) $user['id']]);
        $hash = $statement->fetchColumn();

        if (!$hash || !password_verify($currentPassword, $hash)) {
            flash('danger', 'Mot de passe actuel incorrect.');
            redirect('profile.php');
        }

        if (strlen($newPassword) < 8) {
            flash('danger', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
            redirect('profile.php');
        }

        if ($newPassword !== $confirmPassword) {
            flash('danger', 'La confirmation du nouveau mot de passe ne correspond pas.');
            redirect('profile.php');
        }

        $update = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $update->execute([password_hash($newPassword, PASSWORD_DEFAULT), (int) $user['id']]);
        flash('success', 'Mot de passe mis à jour.');
        redirect('profile.php');
    }
}

$statsStatement = $pdo->prepare(
    'SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_amount), 0) AS total_spent FROM orders WHERE user_id = ?'
);
$statsStatement->execute([(int) $user['id']]);
$stats = $statsStatement->fetch();

$ordersStatement = $pdo->prepare(
    'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10'
);
$ordersStatement->execute([(int) $user['id']]);
$orders = $ordersStatement->fetchAll();

$pageTitle = 'Mon compte';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-banner compact-banner">
    <div class="container">
        <p class="eyebrow">Espace client</p>
        <h1 class="section-title text-white">Mon compte HPROMODE</h1>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="summary-card h-100">
                    <span class="eyebrow">Commandes</span>
                    <strong class="display-6"><?= (int) array_value($stats, 'total_orders', 0) ?></strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card h-100">
                    <span class="eyebrow">Total dépensé</span>
                    <strong class="display-6"><?= format_price((float) array_value($stats, 'total_spent', 0)) ?></strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card h-100">
                    <span class="eyebrow">Statut</span>
                    <strong class="display-6">Client premium</strong>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="glass-panel mb-4">
                    <h2 class="h4 mb-4">Informations personnelles</h2>
                    <form method="post" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="first_name" class="form-control" value="<?= e(array_value($user, 'first_name', '')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom</label>
                            <input type="text" name="last_name" class="form-control" value="<?= e(array_value($user, 'last_name', '')) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= e(array_value($user, 'email', '')) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="phone" class="form-control" value="<?= e(array_value($user, 'phone', '')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="address" class="form-control" value="<?= e(array_value($user, 'address', '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ville</label>
                            <input type="text" name="city" class="form-control" value="<?= e(array_value($user, 'city', '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pays</label>
                            <input type="text" name="country" class="form-control" value="<?= e(array_value($user, 'country', '')) ?>">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-dark" type="submit" name="update_profile" value="1">Mettre à jour</button>
                        </div>
                    </form>
                </div>

                <div class="glass-panel">
                    <h2 class="h4 mb-4">Modifier le mot de passe</h2>
                    <form method="post" class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Mot de passe actuel</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Confirmer</label>
                            <input type="password" name="confirm_new_password" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-dark" type="submit" name="update_password" value="1">Changer le mot de passe</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="glass-panel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <p class="eyebrow mb-1">Historique</p>
                            <h2 class="h4 mb-0">Mes commandes</h2>
                        </div>
                        <a class="btn btn-outline-dark btn-sm" href="<?= e(base_url('shop.php')) ?>">Continuer mes achats</a>
                    </div>
                    <?php if ($orders === []): ?>
                        <p class="small-muted mb-0">Aucune commande pour le moment.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Commande</th>
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
                                            <td><?= e($order['order_number']) ?></td>
                                            <td><?= format_price((float) $order['total_amount']) ?></td>
                                            <td><span class="badge text-bg-<?= badge_class($order['payment_status']) ?>"><?= e($order['payment_status']) ?></span></td>
                                            <td><span class="badge text-bg-<?= badge_class($order['delivery_status']) ?>"><?= e($order['delivery_status']) ?></span></td>
                                            <td><?= e(date('d/m/Y', strtotime($order['created_at']))) ?></td>
                                            <td><a class="btn btn-sm btn-dark" href="<?= e(base_url('order-details.php?id=' . (int) $order['id'])) ?>">Détail</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
