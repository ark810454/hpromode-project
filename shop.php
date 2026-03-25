<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_add'])) {
    $productId = (int) (isset($_POST['product_id']) ? $_POST['product_id'] : 0);
    $statement = $pdo->prepare('SELECT id, stock, color_options, size_options FROM products WHERE id = ? AND is_active = 1');
    $statement->execute(array($productId));
    $product = $statement->fetch();

    if (!$product) {
        flash('danger', 'Produit introuvable.');
        redirect('shop.php');
    }

    if ((int) $product['stock'] <= 0) {
        flash('danger', 'Ce produit est actuellement en rupture de stock.');
        redirect('shop.php');
    }

    $colors = parse_options($product['color_options']);
    $sizes = parse_options($product['size_options']);
    add_to_cart($pdo, $productId, 1, isset($sizes[0]) ? $sizes[0] : '', isset($colors[0]) ? $colors[0] : '');
    flash('success', 'Produit ajoute au panier.');
    redirect('cart.php');
}

$page = max(1, (int) (isset($_GET['page']) ? $_GET['page'] : 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

$search = trim(isset($_GET['search']) ? $_GET['search'] : '');
$categorySlug = trim(isset($_GET['category']) ? $_GET['category'] : '');
$priceRange = trim(isset($_GET['price']) ? $_GET['price'] : '');
$color = trim(isset($_GET['color']) ? $_GET['color'] : '');
$size = trim(isset($_GET['size']) ? $_GET['size'] : '');
$sort = trim(isset($_GET['sort']) ? $_GET['sort'] : 'newest');

$conditions = array('p.is_active = 1');
$params = array();

if ($search !== '') {
    $conditions[] = '(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($categorySlug !== '') {
    $conditions[] = 'c.slug = ?';
    $params[] = $categorySlug;
}

if ($color !== '') {
    $conditions[] = 'p.color_options LIKE ?';
    $params[] = '%' . $color . '%';
}

if ($size !== '') {
    $conditions[] = 'p.size_options LIKE ?';
    $params[] = '%' . $size . '%';
}

if ($priceRange !== '') {
    $priceParts = array_pad(explode('-', $priceRange), 2, null);
    $minPrice = $priceParts[0];
    $maxPrice = $priceParts[1];

    if ($minPrice !== null && $maxPrice !== null) {
        $conditions[] = 'COALESCE(p.promo_price, p.price) BETWEEN ? AND ?';
        $params[] = (float) $minPrice;
        $params[] = (float) $maxPrice;
    }
}

$whereSql = ' WHERE ' . implode(' AND ', $conditions);
if ($sort === 'price_asc') {
    $sortSql = ' ORDER BY COALESCE(p.promo_price, p.price) ASC';
} elseif ($sort === 'price_desc') {
    $sortSql = ' ORDER BY COALESCE(p.promo_price, p.price) DESC';
} elseif ($sort === 'popular') {
    $sortSql = ' ORDER BY p.is_featured DESC, p.stock DESC, p.id DESC';
} else {
    $sortSql = ' ORDER BY p.is_new DESC, p.created_at DESC';
}

$countSql = "SELECT COUNT(*)
             FROM products p
             JOIN categories c ON c.id = p.category_id" . $whereSql;
$countStatement = $pdo->prepare($countSql);
$countStatement->execute($params);
$totalProducts = (int) $countStatement->fetchColumn();
$totalPages = max(1, (int) ceil($totalProducts / $perPage));

$listSql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p
            JOIN categories c ON c.id = p.category_id" . $whereSql . $sortSql . ' LIMIT ? OFFSET ?';
$listStatement = $pdo->prepare($listSql);
$index = 1;
foreach ($params as $param) {
    $listStatement->bindValue($index++, $param);
}
$listStatement->bindValue($index++, $perPage, PDO::PARAM_INT);
$listStatement->bindValue($index, $offset, PDO::PARAM_INT);
$listStatement->execute();
$products = $listStatement->fetchAll();

$categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

$colorOptions = $pdo->query("SELECT DISTINCT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(color_options, ',', numbers.n), ',', -1)) AS color
                             FROM products
                             JOIN (
                                SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
                             ) numbers
                             ON CHAR_LENGTH(color_options) - CHAR_LENGTH(REPLACE(color_options, ',', '')) >= numbers.n - 1
                             WHERE color_options IS NOT NULL AND color_options <> ''
                             ORDER BY color ASC")->fetchAll();

$sizeOptions = $pdo->query("SELECT DISTINCT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(size_options, ',', numbers.n), ',', -1)) AS size
                            FROM products
                            JOIN (
                                SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
                            ) numbers
                            ON CHAR_LENGTH(size_options) - CHAR_LENGTH(REPLACE(size_options, ',', '')) >= numbers.n - 1
                            WHERE size_options IS NOT NULL AND size_options <> ''
                            ORDER BY size ASC")->fetchAll();

$categoryThemes = array(
    'default' => array(
        'label' => 'Boutique HPROMODE',
        'title' => 'Un catalogue de mode pense comme un lookbook commercial premium.',
        'copy' => "Grande respiration, filtres propres, categories elegantes et cartes produit plus sobres pour une lecture digne d'une marque internationale.",
        'image' => asset_url('images/hero.svg'),
    ),
    'robes' => array(
        'label' => 'Atelier Robes',
        'title' => 'Robes de soiree, silhouettes ceremonie et location robe de mariage.',
        'copy' => "Un univers plus feminin et editorial, avec une mise en scene pensee pour la ceremonie, le desir mode et l'accompagnement premium.",
        'image' => asset_url('images/robe-rose.svg'),
    ),
    'costumes' => array(
        'label' => 'Maison Tailoring',
        'title' => 'Costumes structures, allure couture et distinction moderne.',
        'copy' => 'Des lignes plus masculines, plus nettes et plus affirmes pour une lecture tailoring tres premium.',
        'image' => asset_url('images/costume-bleu.svg'),
    ),
    'sacs' => array(
        'label' => 'Salon Maroquinerie',
        'title' => 'Des sacs avec du caractere, du port et une vraie presence mode.',
        'copy' => "Le travail de la matiere, de la couleur et du volume devient un langage de desir tres visuel.",
        'image' => asset_url('images/sac-bordeaux.svg'),
    ),
    'bijoux' => array(
        'label' => 'Ecrin Bijoux',
        'title' => "Des bijoux penses comme des accents d'elegance et de lumiere.",
        'copy' => "Moins de bruit visuel, plus d'eclat, plus de raffinement et une lecture plus mode des accessoires.",
        'image' => asset_url('images/bracelet.svg'),
    ),
);

$activeTheme = isset($categoryThemes[$categorySlug]) ? $categoryThemes[$categorySlug] : $categoryThemes['default'];
$pageTitle = $categorySlug !== '' && isset($categoryThemes[$categorySlug]) ? ucfirst($categorySlug) : 'Boutique';
$queryParams = $_GET;

require_once __DIR__ . '/includes/header.php';
?>

<section class="catalog-hero">
    <div class="container">
        <div class="catalog-hero-shell reveal-up">
            <div class="catalog-hero-grid">
                <div class="catalog-hero-copy">
                    <p class="eyebrow text-gold"><?= e($activeTheme['label']) ?></p>
                    <h1 class="section-title text-white"><?= e($activeTheme['title']) ?></h1>
                    <p class="catalog-hero-copy-text text-white-50"><?= e($activeTheme['copy']) ?></p>
                    <div class="home-hero-actions">
                        <a class="btn btn-luxury btn-lg" href="#catalogue">Voir la selection</a>
                        <?php if ($categorySlug === 'robes'): ?>
                            <a class="btn btn-outline-light btn-lg" href="#location-mariage">Location mariage</a>
                        <?php else: ?>
                            <a class="btn btn-outline-light btn-lg" href="<?= e(base_url('shop.php?sort=popular')) ?>">Best sellers</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="catalog-hero-media">
                    <img src="<?= e($activeTheme['image']) ?>" alt="<?= e($activeTheme['label']) ?>">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <div class="category-shortcuts">
            <?php foreach ($categories as $category): ?>
                <?php
                $theme = isset($categoryThemes[$category['slug']]) ? $categoryThemes[$category['slug']] : $categoryThemes['default'];
                $isActiveCategory = $categorySlug === $category['slug'];
                ?>
                <a class="category-shortcut <?= $isActiveCategory ? 'is-active' : '' ?>" href="<?= e(base_url('shop.php?category=' . urlencode($category['slug']))) ?>">
                    <img src="<?= e($theme['image']) ?>" alt="<?= e($category['name']) ?>">
                    <span><?= e($category['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($categorySlug === 'robes'): ?>
    <section class="section-block pt-0" id="location-mariage">
        <div class="container">
            <div class="rental-atelier reveal-up">
                <div class="rental-visual">
                    <img src="<?= e(asset_url('images/wedding-rental.svg')) ?>" alt="Location robe de mariage HPROMODE">
                </div>
                <div class="rental-copy">
                    <p class="eyebrow text-gold">Location robe de mariage</p>
                    <h2 class="section-title text-white">Une experience ceremonie plus exclusive et plus accompagnee.</h2>
                    <p class="text-white-50">
                        HPROMODE ajoute a l'univers robes un service de location premium avec essayage prive,
                        conseils silhouette et finitions style pour les grandes occasions.
                    </p>
                    <div class="rental-grid">
                        <div class="rental-card">
                            <span>Essayage prive</span>
                            <strong>Selection guidee selon la ceremonie, la morphologie et le style souhaite.</strong>
                        </div>
                        <div class="rental-card">
                            <span>Accessoires</span>
                            <strong>Voile, bijoux, sac et finitions selon les pieces disponibles.</strong>
                        </div>
                        <div class="rental-card">
                            <span>Reservation</span>
                            <strong>Un parcours simple, rassurant et plus premium que la location classique.</strong>
                        </div>
                    </div>
                    <div class="home-hero-actions">
                        <a class="btn btn-luxury btn-lg" href="mailto:<?= e(APP_SUPPORT_EMAIL) ?>?subject=Reservation%20Robe%20de%20Mariage">Reserver un essayage</a>
                        <a class="btn btn-outline-light btn-lg" href="tel:<?= e(APP_SUPPORT_PHONE) ?>">Contacter la conseillere</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="section-block pt-0" id="catalogue">
    <div class="container">
        <div class="catalog-shell">
            <aside class="filter-card catalog-filter-card">
                <div class="filter-stack-head">
                    <p class="eyebrow">Filtres</p>
                    <h2 class="h4 mb-0">Affiner la selection</h2>
                </div>
                <form method="get" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Recherche</label>
                        <input type="text" name="search" class="form-control" value="<?= e($search) ?>" placeholder="Nom, description ou SKU">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Categorie</label>
                        <select name="category" class="form-select">
                            <option value="">Toutes les categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= e($category['slug']) ?>" <?= $categorySlug === $category['slug'] ? 'selected' : '' ?>>
                                    <?= e($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Prix</label>
                        <select name="price" class="form-select">
                            <option value="">Tous les prix</option>
                            <option value="0-100" <?= $priceRange === '0-100' ? 'selected' : '' ?>>0 $ a 100 $</option>
                            <option value="100-200" <?= $priceRange === '100-200' ? 'selected' : '' ?>>100 $ a 200 $</option>
                            <option value="200-400" <?= $priceRange === '200-400' ? 'selected' : '' ?>>200 $ a 400 $</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Couleur</label>
                        <select name="color" class="form-select">
                            <option value="">Toutes les couleurs</option>
                            <?php foreach ($colorOptions as $option): ?>
                                <?php if (trim((string) $option['color']) === '') continue; ?>
                                <option value="<?= e($option['color']) ?>" <?= $color === $option['color'] ? 'selected' : '' ?>>
                                    <?= e($option['color']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Taille</label>
                        <select name="size" class="form-select">
                            <option value="">Toutes les tailles</option>
                            <?php foreach ($sizeOptions as $option): ?>
                                <?php if (trim((string) $option['size']) === '') continue; ?>
                                <option value="<?= e($option['size']) ?>" <?= $size === $option['size'] ? 'selected' : '' ?>>
                                    <?= e($option['size']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Tri</label>
                        <select name="sort" class="form-select">
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Nouveautes</option>
                            <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Popularite</option>
                            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Prix decroissant</option>
                        </select>
                    </div>
                    <div class="col-12 d-grid gap-2">
                        <button class="btn btn-dark" type="submit">Appliquer</button>
                        <a class="btn btn-outline-dark" href="<?= e(base_url('shop.php')) ?>">Reinitialiser</a>
                    </div>
                </form>
            </aside>

            <div class="catalog-content">
                <div class="collection-panel">
                    <div class="catalog-toolbar">
                        <div>
                            <p class="eyebrow mb-1">Catalogue</p>
                            <h2 class="section-title mb-0"><?= $totalProducts ?> produit<?= $totalProducts > 1 ? 's' : '' ?></h2>
                        </div>
                        <p class="small-muted mb-0">Une grille plus respiree, une lecture plus mode et des cartes plus desirables.</p>
                    </div>
                </div>

                <?php if ($products === array()): ?>
                    <div class="glass-panel text-center py-5 mt-4">
                        <h3>Aucun produit ne correspond a vos filtres.</h3>
                        <p class="small-muted">Essayez une autre combinaison de categorie, prix, couleur ou taille.</p>
                        <a class="btn btn-dark" href="<?= e(base_url('shop.php')) ?>">Voir toute la collection</a>
                    </div>
                <?php else: ?>
                    <div class="product-grid mt-4">
                        <?php foreach ($products as $product): ?>
                            <article class="product-card reveal-up">
                                <a class="product-card-link" href="<?= e(base_url('product-details.php?id=' . (int) $product['id'])) ?>">
                                    <div class="product-card-media">
                                        <img src="<?= e($product['main_image']) ?>" alt="<?= e($product['name']) ?>">
                                        <span class="product-chip"><?= e($product['category_name']) ?></span>
                                    </div>
                                    <div class="product-card-body">
                                        <div class="product-card-header">
                                            <div>
                                                <h3><?= e($product['name']) ?></h3>
                                                <p><?= e(mb_strimwidth($product['description'], 0, 90, '...')) ?></p>
                                            </div>
                                            <?php if (!empty($product['promo_price'])): ?>
                                                <span class="product-badge">Promo</span>
                                            <?php elseif (!empty($product['is_new'])): ?>
                                                <span class="product-badge">Nouveau</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-meta">
                                            <span>Couleurs : <?= e($product['color_options'] ?: 'Standard') ?></span>
                                            <span>Tailles : <?= e($product['size_options'] ?: 'Unique') ?></span>
                                        </div>
                                        <div class="product-card-footer">
                                            <div>
                                                <?php if (!empty($product['promo_price'])): ?>
                                                    <span class="old-price"><?= format_price((float) $product['price']) ?></span>
                                                <?php endif; ?>
                                                <strong><?= format_price((float) ($product['promo_price'] ?: $product['price'])) ?></strong>
                                            </div>
                                            <span class="product-card-cta">Voir le produit</span>
                                        </div>
                                    </div>
                                </a>
                                <form method="post" class="product-card-action">
                                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                    <button class="btn btn-outline-dark btn-sm" name="quick_add" value="1" type="submit">Ajouter au panier</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav class="mt-4" aria-label="Pagination boutique">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php $queryParams['page'] = $i; ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= e(base_url('shop.php?' . http_build_query($queryParams))) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
