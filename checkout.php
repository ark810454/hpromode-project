<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$totals = calculate_cart_totals($pdo);
$items = $totals['items'];

if ($items === []) {
    flash('danger', 'Votre panier est vide.');
    redirect('shop.php');
}

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requiredFields = [
        'first_name', 'last_name', 'phone', 'email', 'address',
        'city', 'country', 'delivery_zone', 'delivery_method', 'payment_method',
    ];

    foreach ($requiredFields as $field) {
        if (trim(isset($_POST[$field]) ? $_POST[$field] : '') === '') {
            flash('danger', 'Veuillez remplir tous les champs obligatoires.');
            redirect('checkout.php');
        }
    }

    $deliveryMethod = trim($_POST['delivery_method']);
    $deliveryZone = trim($_POST['delivery_zone']);
    $paymentMethod = trim($_POST['payment_method']);
    $deliveryFee = determine_delivery_fee($deliveryMethod, $deliveryZone);
    $finalTotal = $totals['total'] + $deliveryFee;
    $paymentStatus = strpos(mb_strtolower($paymentMethod, 'UTF-8'), 'carte') !== false ? 'validé' : 'en attente';

    if ($paymentStatus === 'validé') {
        $cardNumber = preg_replace('/\D+/', '', isset($_POST['card_number']) ? $_POST['card_number'] : '');
        $cardHolder = trim(isset($_POST['card_name']) ? $_POST['card_name'] : '');
        $cardExpiry = trim(isset($_POST['card_expiry']) ? $_POST['card_expiry'] : '');
        $cardCvv = preg_replace('/\D+/', '', isset($_POST['card_cvv']) ? $_POST['card_cvv'] : '');

        if ($cardHolder === '' || strlen($cardNumber) < 12 || $cardExpiry === '' || strlen($cardCvv) < 3) {
            flash('danger', 'Veuillez compléter correctement les informations de carte pour la simulation.');
            redirect('checkout.php');
        }
    }

    $pdo->beginTransaction();

    try {
        $orderNumber = generate_order_number();

        $updateUser = $pdo->prepare(
            'UPDATE users SET first_name = ?, last_name = ?, phone = ?, email = ?, address = ?, city = ?, country = ? WHERE id = ?'
        );
        $updateUser->execute([
            trim($_POST['first_name']),
            trim($_POST['last_name']),
            trim($_POST['phone']),
            trim($_POST['email']),
            trim($_POST['address']),
            trim($_POST['city']),
            trim($_POST['country']),
            (int) $user['id'],
        ]);

        $orderStatement = $pdo->prepare(
            'INSERT INTO orders (
                user_id, order_number, first_name, last_name, phone, email, address, city, country,
                subtotal, delivery_fee, discount_amount, total_amount, delivery_method, delivery_zone,
                payment_method, payment_status, delivery_status, promo_code, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $orderStatement->execute([
            (int) $user['id'],
            $orderNumber,
            trim($_POST['first_name']),
            trim($_POST['last_name']),
            trim($_POST['phone']),
            trim($_POST['email']),
            trim($_POST['address']),
            trim($_POST['city']),
            trim($_POST['country']),
            $totals['subtotal'],
            $deliveryFee,
            $totals['discount'],
            $finalTotal,
            $deliveryMethod,
            $deliveryZone,
            $paymentMethod,
            $paymentStatus,
            'en attente',
            $totals['promotion'] ? $totals['promotion']['code'] : null,
            trim(isset($_POST['notes']) ? $_POST['notes'] : ''),
        ]);

        $orderId = (int) $pdo->lastInsertId();

        $itemStatement = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, size, color, quantity, unit_price, line_total)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stockStatement = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');

        foreach ($items as $item) {
            $product = $item['product'];
            if ((int) $product['stock'] < (int) $item['quantity']) {
                throw new RuntimeException('Stock insuffisant pour ' . $product['name'] . '.');
            }

            $itemStatement->execute([
                $orderId,
                (int) $product['id'],
                $product['name'],
                $item['size'],
                $item['color'],
                (int) $item['quantity'],
                (float) $item['unit_price'],
                (float) $item['line_total'],
            ]);

            $stockStatement->execute([(int) $item['quantity'], (int) $product['id']]);
        }

        $paymentReference = 'PAY-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        $paymentStatement = $pdo->prepare(
            'INSERT INTO payments (order_id, payment_method, amount, status, transaction_reference, payment_note)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $paymentStatement->execute([
            $orderId,
            $paymentMethod,
            $finalTotal,
            $paymentStatus,
            $paymentReference,
            $paymentStatus === 'validé' ? 'Paiement carte simulé validé.' : 'Paiement en attente de traitement.',
        ]);

        $deliveryStatement = $pdo->prepare(
            'INSERT INTO deliveries (order_id, delivery_method, delivery_zone, delivery_city, fee, status, tracking_number)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $deliveryStatement->execute([
            $orderId,
            $deliveryMethod,
            $deliveryZone,
            trim($_POST['city']),
            $deliveryFee,
            'en attente',
            'TRK-' . strtoupper(substr(md5(uniqid('', true)), 0, 6)),
        ]);

        $pdo->commit();

        sync_user_session($pdo, (int) $user['id']);
        clear_cart($pdo);

        flash(
            'success',
            $paymentStatus === 'validé'
                ? 'Commande confirmée et paiement validé. Numéro : ' . $orderNumber
                : 'Commande enregistrée. Paiement en attente. Numéro : ' . $orderNumber
        );
        redirect('order-details.php?id=' . $orderId);
    } catch (Exception $throwable) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        flash('danger', 'Impossible de finaliser la commande : ' . $throwable->getMessage());
        redirect('checkout.php');
    }
}

$pageTitle = 'Validation de commande';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-banner compact-banner">
    <div class="container">
        <p class="eyebrow">Validation de commande</p>
        <h1 class="section-title text-white">Finalisez votre expérience d’achat HPROMODE.</h1>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <form method="post" class="glass-panel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="first_name" class="form-control" value="<?= e(array_value($user, 'first_name', '')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom</label>
                            <input type="text" name="last_name" class="form-control" value="<?= e(array_value($user, 'last_name', '')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="phone" class="form-control" value="<?= e(array_value($user, 'phone', '')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= e(array_value($user, 'email', '')) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="address" class="form-control" value="<?= e(array_value($user, 'address', '')) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ville</label>
                            <input type="text" name="city" class="form-control" value="<?= e(array_value($user, 'city', '')) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pays</label>
                            <input type="text" name="country" class="form-control" value="<?= e(array_value($user, 'country', 'Nigeria')) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Zone de livraison</label>
                            <select name="delivery_zone" class="form-select" required>
                                <option value="centre-ville">Centre-ville</option>
                                <option value="national">National</option>
                                <option value="international">International</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mode de livraison</label>
                            <select name="delivery_method" class="form-select" required>
                                <option value="livraison standard">Livraison standard</option>
                                <option value="livraison express">Livraison express</option>
                                <option value="retrait boutique">Retrait boutique</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mode de paiement</label>
                            <select name="payment_method" class="form-select" data-payment-method required>
                                <option value="Paiement à la livraison">Paiement à la livraison</option>
                                <option value="Virement bancaire">Virement bancaire</option>
                                <option value="Mobile Money">Mobile Money</option>
                                <option value="Carte bancaire (simulation)">Carte bancaire (simulation)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="card-simulation-fields d-none" data-card-fields>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nom sur la carte</label>
                                        <input type="text" name="card_name" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Numéro de carte</label>
                                        <input type="text" name="card_number" class="form-control" placeholder="4242 4242 4242 4242">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Expiration</label>
                                        <input type="text" name="card_expiry" class="form-control" placeholder="12/30">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">CVV</label>
                                        <input type="text" name="card_cvv" class="form-control" placeholder="123">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Instructions complémentaires</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Appartement, point de repère, informations utiles..."></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-luxury btn-lg" type="submit">Valider la commande</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-5">
                <div class="summary-card">
                    <h2 class="h4 mb-4">Résumé de commande</h2>
                    <?php foreach ($items as $item): ?>
                        <div class="summary-line">
                            <span><?= e($item['product']['name']) ?> × <?= (int) $item['quantity'] ?></span>
                            <strong><?= format_price((float) $item['line_total']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="summary-line">
                        <span>Sous-total</span>
                        <strong><?= format_price((float) $totals['subtotal']) ?></strong>
                    </div>
                    <div class="summary-line">
                        <span>Remise</span>
                        <strong>- <?= format_price((float) $totals['discount']) ?></strong>
                    </div>
                    <div class="summary-line">
                        <span>Livraison estimée</span>
                        <strong>Calculée selon la zone</strong>
                    </div>
                    <div class="summary-line total-line">
                        <span>Total avant livraison</span>
                        <strong><?= format_price((float) $totals['total']) ?></strong>
                    </div>
                    <p class="small-muted mt-3">Un numéro de commande unique sera généré après validation.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
