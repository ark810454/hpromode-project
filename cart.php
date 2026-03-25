<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_item'])) {
        remove_from_cart($pdo, (string) $_POST['remove_item']);
        flash('success', 'Article retiré du panier.');
        redirect('cart.php');
    }

    if (isset($_POST['update_cart'])) {
        $postedQuantities = isset($_POST['quantities']) ? $_POST['quantities'] : array();
        foreach ($postedQuantities as $cartKey => $quantity) {
            if (!isset($_SESSION['cart'][$cartKey])) {
                continue;
            }

            $productId = (int) $_SESSION['cart'][$cartKey]['product_id'];
            $statement = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
            $statement->execute([$productId]);
            $stock = (int) $statement->fetchColumn();

            if ($stock <= 0) {
                unset($_SESSION['cart'][$cartKey]);
                continue;
            }

            $_SESSION['cart'][$cartKey]['quantity'] = min(max(1, (int) $quantity), $stock);
        }

        persist_cart($pdo);
        flash('success', 'Panier mis à jour.');
        redirect('cart.php');
    }

    if (isset($_POST['apply_promo'])) {
        $code = trim(isset($_POST['promo_code']) ? $_POST['promo_code'] : '');
        $promotion = get_active_promotion($pdo, $code);

        if ($promotion) {
            set_promo_code($promotion['code']);
            flash('success', 'Code promotionnel appliqué.');
        } else {
            set_promo_code(null);
            flash('danger', 'Code promotionnel invalide ou inactif.');
        }

        redirect('cart.php');
    }

    if (isset($_POST['clear_promo'])) {
        set_promo_code(null);
        flash('success', 'Code promotionnel retiré.');
        redirect('cart.php');
    }
}

$totals = calculate_cart_totals($pdo);
$items = $totals['items'];
$pageTitle = 'Panier';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-banner compact-banner">
    <div class="container">
        <p class="eyebrow">Panier HPROMODE</p>
        <h1 class="section-title text-white">Votre sélection premium</h1>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if ($items === []): ?>
            <div class="glass-panel text-center py-5">
                <h2 class="section-title">Votre panier est vide.</h2>
                <p class="small-muted">Explorez la collection HPROMODE pour ajouter robes, costumes et accessoires premium.</p>
                <a class="btn btn-dark" href="<?= e(base_url('shop.php')) ?>">Continuer les achats</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <form method="post" class="glass-panel">
                        <div class="table-responsive">
                            <table class="table cart-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Prix</th>
                                        <th>Quantité</th>
                                        <th>Sous-total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="<?= e($item['product']['main_image']) ?>" alt="<?= e($item['product']['name']) ?>" class="cart-thumb">
                                                    <div>
                                                        <strong><?= e($item['product']['name']) ?></strong>
                                                        <div class="small-muted">Couleur : <?= e($item['color'] ?: 'Unique') ?></div>
                                                        <div class="small-muted">Taille : <?= e($item['size'] ?: 'Unique') ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= format_price((float) $item['unit_price']) ?></td>
                                            <td>
                                                <input
                                                    type="number"
                                                    min="1"
                                                    max="<?= (int) $item['product']['stock'] ?>"
                                                    class="form-control"
                                                    style="max-width: 110px;"
                                                    name="quantities[<?= e($item['key']) ?>]"
                                                    value="<?= (int) $item['quantity'] ?>"
                                                >
                                            </td>
                                            <td><?= format_price((float) $item['line_total']) ?></td>
                                            <td>
                                                <button class="btn btn-outline-danger btn-sm" type="submit" name="remove_item" value="<?= e($item['key']) ?>">Supprimer</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4">
                            <a class="btn btn-outline-dark" href="<?= e(base_url('shop.php')) ?>">Continuer les achats</a>
                            <button class="btn btn-dark" type="submit" name="update_cart" value="1">Mettre à jour</button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4">
                    <div class="glass-panel mb-4">
                        <h2 class="h4 mb-3">Code promo</h2>
                        <form method="post" class="row g-2">
                            <div class="col-12">
                                <input type="text" class="form-control" name="promo_code" value="<?= e(get_promo_code()) ?>" placeholder="Ex. HPRO10">
                            </div>
                            <div class="col-12 d-grid gap-2">
                                <button class="btn btn-dark" type="submit" name="apply_promo" value="1">Appliquer</button>
                                <?php if (get_promo_code() !== null): ?>
                                    <button class="btn btn-outline-secondary" type="submit" name="clear_promo" value="1">Retirer</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <div class="summary-card">
                        <h2 class="h4 mb-4">Résumé du panier</h2>
                        <div class="summary-line">
                            <span>Sous-total</span>
                            <strong><?= format_price((float) $totals['subtotal']) ?></strong>
                        </div>
                        <div class="summary-line">
                            <span>Remise</span>
                            <strong>- <?= format_price((float) $totals['discount']) ?></strong>
                        </div>
                        <div class="summary-line total-line">
                            <span>Total produits</span>
                            <strong><?= format_price((float) $totals['total']) ?></strong>
                        </div>
                        <p class="small-muted mt-3">Les frais de livraison seront calculés à l’étape de validation selon le mode choisi.</p>
                        <div class="d-grid gap-2 mt-4">
                            <a class="btn btn-luxury" href="<?= e(base_url('checkout.php')) ?>">Passer à la commande</a>
                            <a class="btn btn-outline-dark" href="<?= e(base_url('shop.php')) ?>">Voir la collection</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
