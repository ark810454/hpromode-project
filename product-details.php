<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$productId = (int) (isset($_GET['id']) ? $_GET['id'] : 0);
$statement = $pdo->prepare(
    "SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? AND p.is_active = 1"
);
$statement->execute(array($productId));
$product = $statement->fetch();

if (!$product) {
    flash('danger', 'Produit introuvable.');
    redirect('shop.php');
}

$sizeOptions = parse_options($product['size_options']);
$colorOptions = parse_options($product['color_options']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = max(1, (int) (isset($_POST['quantity']) ? $_POST['quantity'] : 1));
    $selectedSize = trim(isset($_POST['size']) ? $_POST['size'] : (isset($sizeOptions[0]) ? $sizeOptions[0] : ''));
    $selectedColor = trim(isset($_POST['color']) ? $_POST['color'] : (isset($colorOptions[0]) ? $colorOptions[0] : ''));
    $buyNow = isset($_POST['buy_now']);

    if ($sizeOptions !== array() && !in_array($selectedSize, $sizeOptions, true)) {
        flash('danger', 'Veuillez selectionner une taille valide.');
        redirect('product-details.php?id=' . $productId);
    }

    if ($colorOptions !== array() && !in_array($selectedColor, $colorOptions, true)) {
        flash('danger', 'Veuillez selectionner une couleur valide.');
        redirect('product-details.php?id=' . $productId);
    }

    if ((int) $product['stock'] <= 0) {
        flash('danger', 'Ce produit est en rupture de stock.');
        redirect('product-details.php?id=' . $productId);
    }

    if ($quantity > (int) $product['stock']) {
        flash('danger', 'La quantite demandee depasse le stock disponible.');
        redirect('product-details.php?id=' . $productId);
    }

    add_to_cart($pdo, $productId, $quantity, $selectedSize, $selectedColor);
    flash('success', $buyNow ? 'Produit ajoute. Vous pouvez finaliser la commande.' : 'Produit ajoute au panier.');
    redirect($buyNow ? 'checkout.php' : 'cart.php');
}

$gallery = product_gallery($pdo, $productId, $product['main_image']);
$relatedStatement = $pdo->prepare(
    "SELECT * FROM products
     WHERE category_id = ? AND id <> ? AND is_active = 1
     ORDER BY is_featured DESC, created_at DESC
     LIMIT 4"
);
$relatedStatement->execute(array((int) $product['category_id'], $productId));
$relatedProducts = $relatedStatement->fetchAll();

$completeStatement = $pdo->prepare(
    "SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.id <> ? AND p.is_active = 1
     ORDER BY p.is_featured DESC, p.created_at DESC
     LIMIT 3"
);
$completeStatement->execute(array($productId));
$completeProducts = $completeStatement->fetchAll();

$pageTitle = $product['name'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="product-stage">
    <div class="container">
        <div class="product-stage-grid">
            <div class="product-stage-gallery reveal-up">
                <div class="product-gallery-main">
                    <img
                        src="<?= e($gallery[0]['image_path']) ?>"
                        alt="<?= e($gallery[0]['alt_text'] ?: $product['name']) ?>"
                        data-gallery-main
                    >
                </div>
                <?php if (count($gallery) > 1): ?>
                    <div class="product-thumbs">
                        <?php foreach ($gallery as $index => $image): ?>
                            <button
                                type="button"
                                class="thumb-button <?= $index === 0 ? 'is-active' : '' ?>"
                                data-gallery-thumb
                                data-image="<?= e($image['image_path']) ?>"
                                data-alt="<?= e($image['alt_text'] ?: $product['name']) ?>"
                            >
                                <img src="<?= e($image['image_path']) ?>" alt="<?= e($image['alt_text'] ?: $product['name']) ?>">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-stage-copy reveal-up">
                <p class="eyebrow"><?= e($product['category_name']) ?></p>
                <h1 class="section-title product-title"><?= e($product['name']) ?></h1>
                <p class="product-lead"><?= e($product['description']) ?></p>

                <div class="price-block">
                    <?php if (!empty($product['promo_price'])): ?>
                        <span class="old-price"><?= format_price((float) $product['price']) ?></span>
                    <?php endif; ?>
                    <strong><?= format_price((float) ($product['promo_price'] ?: $product['price'])) ?></strong>
                </div>

                <div class="product-meta-rail">
                    <div>
                        <span>Disponibilite</span>
                        <strong><?= e(stock_state((int) $product['stock'])) ?></strong>
                    </div>
                    <div>
                        <span>Stock</span>
                        <strong><?= (int) $product['stock'] ?> unite(s)</strong>
                    </div>
                    <div>
                        <span>SKU</span>
                        <strong><?= e($product['sku']) ?></strong>
                    </div>
                    <div>
                        <span>Signature</span>
                        <strong>Editorial premium HPROMODE</strong>
                    </div>
                </div>

                <form method="post" class="product-buy-card">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Taille</label>
                            <select name="size" class="form-select" <?= $sizeOptions === array() ? 'disabled' : '' ?>>
                                <?php if ($sizeOptions === array()): ?>
                                    <option value="">Taille unique</option>
                                <?php else: ?>
                                    <?php foreach ($sizeOptions as $option): ?>
                                        <option value="<?= e($option) ?>"><?= e($option) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Couleur</label>
                            <select name="color" class="form-select" <?= $colorOptions === array() ? 'disabled' : '' ?>>
                                <?php if ($colorOptions === array()): ?>
                                    <option value="">Couleur unique</option>
                                <?php else: ?>
                                    <?php foreach ($colorOptions as $option): ?>
                                        <option value="<?= e($option) ?>"><?= e($option) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantite</label>
                            <input type="number" min="1" max="<?= (int) $product['stock'] ?>" name="quantity" value="1" class="form-control">
                        </div>
                        <div class="col-md-8 d-flex align-items-end gap-2 flex-wrap">
                            <button class="btn btn-dark btn-lg" type="submit">Ajouter au panier</button>
                            <button class="btn btn-outline-dark btn-lg" type="submit" name="buy_now" value="1">Acheter maintenant</button>
                        </div>
                    </div>
                </form>

                <div class="product-editorial-note">
                    <p class="eyebrow">L'esprit maison</p>
                    <p>
                        Cette fiche produit est pensee comme une page mode premium:
                        image dominante, informations claires, formulaire epure et accessoires relies a l'allure globale.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-block pt-0">
    <div class="container">
        <div class="product-story-grid">
            <div class="quote-panel reveal-up">
                <p class="eyebrow">L'elegance au quotidien</p>
                <h2 class="section-title">Une piece pensee pour durer dans le regard.</h2>
                <p>
                    Chaque article HPROMODE est presente dans un environnement plus sobre, plus aerien
                    et plus inspire du retail mode luxe afin de renforcer desir, confiance et projection.
                </p>
            </div>
            <div class="quote-panel quote-panel-dark reveal-up">
                <p class="eyebrow text-gold">Completer le look</p>
                <h2 class="section-title text-white">Associez vetement et accessoires dans un meme geste.</h2>
                <p class="text-white-50 mb-0">
                    Robes, sacs, bijoux et pieces tailoring s'articulent ici comme une silhouette complete,
                    a la maniere d'une maison de mode qui pense l'allure avant le simple article.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section-block pt-0">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Completer le look</p>
                <h2 class="section-title">Suggestions pour enrichir la silhouette.</h2>
            </div>
        </div>
        <div class="product-grid">
            <?php foreach ($completeProducts as $complete): ?>
                <article class="product-card reveal-up">
                    <a class="product-card-link" href="<?= e(base_url('product-details.php?id=' . (int) $complete['id'])) ?>">
                        <div class="product-card-media">
                            <img src="<?= e($complete['main_image']) ?>" alt="<?= e($complete['name']) ?>">
                            <span class="product-chip"><?= e($complete['category_name']) ?></span>
                        </div>
                        <div class="product-card-body">
                            <div class="product-card-header">
                                <div>
                                    <h3><?= e($complete['name']) ?></h3>
                                    <p><?= e(mb_strimwidth($complete['description'], 0, 80, '...')) ?></p>
                                </div>
                            </div>
                            <div class="product-card-footer">
                                <strong><?= format_price((float) ($complete['promo_price'] ?: $complete['price'])) ?></strong>
                                <span class="product-card-cta">Voir le produit</span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-block pt-0">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Produits similaires</p>
                <h2 class="section-title">Autres pieces de la meme collection.</h2>
            </div>
        </div>
        <div class="product-grid">
            <?php foreach ($relatedProducts as $related): ?>
                <article class="product-card reveal-up">
                    <a class="product-card-link" href="<?= e(base_url('product-details.php?id=' . (int) $related['id'])) ?>">
                        <div class="product-card-media">
                            <img src="<?= e($related['main_image']) ?>" alt="<?= e($related['name']) ?>">
                            <span class="product-chip"><?= e($product['category_name']) ?></span>
                        </div>
                        <div class="product-card-body">
                            <div class="product-card-header">
                                <div>
                                    <h3><?= e($related['name']) ?></h3>
                                    <p><?= e(mb_strimwidth($related['description'], 0, 80, '...')) ?></p>
                                </div>
                            </div>
                            <div class="product-card-footer">
                                <strong><?= format_price((float) ($related['promo_price'] ?: $related['price'])) ?></strong>
                                <span class="product-card-cta">Voir le produit</span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
