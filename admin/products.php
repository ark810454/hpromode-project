<?php
$pageTitle = 'Articles';
require_once __DIR__ . '/includes_top.php';

$editingProduct = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_product'])) {
        $productId = (int) (isset($_POST['product_id']) ? $_POST['product_id'] : 0);
        $categoryId = (int) (isset($_POST['category_id']) ? $_POST['category_id'] : 0);
        $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
        $slug = trim(isset($_POST['slug']) ? $_POST['slug'] : '');
        $sku = trim(isset($_POST['sku']) ? $_POST['sku'] : '');
        $description = trim(isset($_POST['description']) ? $_POST['description'] : '');
        $price = (float) (isset($_POST['price']) ? $_POST['price'] : 0);
        $promoInput = isset($_POST['promo_price']) ? $_POST['promo_price'] : '';
        $promoPrice = trim($promoInput) !== '' ? (float) $promoInput : null;
        $stock = max(0, (int) (isset($_POST['stock']) ? $_POST['stock'] : 0));
        $colorOptions = trim(isset($_POST['color_options']) ? $_POST['color_options'] : '');
        $sizeOptions = trim(isset($_POST['size_options']) ? $_POST['size_options'] : '');
        $mainImage = trim(isset($_POST['main_image']) ? $_POST['main_image'] : '');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isNew = isset($_POST['is_new']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($categoryId <= 0 || $name === '' || $description === '' || $price <= 0 || $mainImage === '') {
            flash('danger', 'Veuillez completer tous les champs obligatoires de l article.');
            redirect('admin/products.php' . ($productId ? '?edit=' . $productId : ''));
        }

        $slug = $slug !== '' ? generate_slug($slug) : generate_slug($name);
        $sku = $sku !== '' ? $sku : strtoupper(substr($slug, 0, 4)) . '-' . mt_rand(1000, 9999);

        if ($productId > 0) {
            $statement = $pdo->prepare(
                'UPDATE products SET category_id = ?, name = ?, slug = ?, sku = ?, description = ?, price = ?, promo_price = ?, stock = ?,
                 color_options = ?, size_options = ?, main_image = ?, is_featured = ?, is_new = ?, is_active = ? WHERE id = ?'
            );
            $statement->execute(array(
                $categoryId, $name, $slug, $sku, $description, $price, $promoPrice, $stock,
                $colorOptions, $sizeOptions, $mainImage, $isFeatured, $isNew, $isActive, $productId,
            ));
            flash('success', 'Article mis a jour.');
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO products (
                    category_id, name, slug, sku, description, price, promo_price, stock,
                    color_options, size_options, main_image, is_featured, is_new, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $statement->execute(array(
                $categoryId, $name, $slug, $sku, $description, $price, $promoPrice, $stock,
                $colorOptions, $sizeOptions, $mainImage, $isFeatured, $isNew, $isActive,
            ));
            flash('success', 'Article ajoute.');
        }

        redirect('admin/products.php');
    }

    if (isset($_POST['delete_product'])) {
        $productId = (int) (isset($_POST['product_id']) ? $_POST['product_id'] : 0);
        $statement = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $statement->execute(array($productId));
        flash('success', 'Article supprime.');
        redirect('admin/products.php');
    }
}

if (isset($_GET['edit'])) {
    $statement = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $statement->execute(array((int) $_GET['edit']));
    $editingProduct = $statement->fetch() ?: null;
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
$products = $pdo->query(
    'SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     ORDER BY p.created_at DESC'
)->fetchAll();
?>

<div class="row g-4">
    <div class="col-xl-5">
        <div class="glass-panel">
            <p class="eyebrow"><?= $editingProduct ? 'Modifier' : 'Nouvel article' ?></p>
            <h3 class="h4 mb-4"><?= $editingProduct ? 'Mettre a jour un article' : 'Ajouter un article' ?></h3>
            <form method="post" class="row g-3">
                <input type="hidden" name="product_id" value="<?= (int) array_value($editingProduct, 'id', 0) ?>">
                <div class="col-md-6">
                    <label class="form-label">Categorie</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Choisir</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= (int) array_value($editingProduct, 'category_id', 0) === (int) $category['id'] ? 'selected' : '' ?>>
                                <?= e($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="<?= e(array_value($editingProduct, 'sku', '')) ?>" placeholder="Genere si vide">
                </div>
                <div class="col-12">
                    <label class="form-label">Nom de l article</label>
                    <input type="text" name="name" class="form-control" value="<?= e(array_value($editingProduct, 'name', '')) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?= e(array_value($editingProduct, 'slug', '')) ?>" placeholder="Genere automatiquement si vide">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" required><?= e(array_value($editingProduct, 'description', '')) ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prix</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= e((string) array_value($editingProduct, 'price', '')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prix promo</label>
                    <input type="number" step="0.01" name="promo_price" class="form-control" value="<?= e((string) array_value($editingProduct, 'promo_price', '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" value="<?= e((string) array_value($editingProduct, 'stock', 0)) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Couleurs</label>
                    <input type="text" name="color_options" class="form-control" value="<?= e(array_value($editingProduct, 'color_options', '')) ?>" placeholder="Bordeaux, Noir, Bleu royal">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tailles</label>
                    <input type="text" name="size_options" class="form-control" value="<?= e(array_value($editingProduct, 'size_options', '')) ?>" placeholder="XS, S, M, L">
                </div>
                <div class="col-12">
                    <label class="form-label">Image principale</label>
                    <input type="text" name="main_image" class="form-control" value="<?= e(array_value($editingProduct, 'main_image', asset_url('images/product-default.svg'))) ?>" required>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" <?= !empty($editingProduct['is_featured']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Article vedette</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_new" id="is_new" <?= !empty($editingProduct['is_new']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_new">Nouveaute</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= !isset($editingProduct['is_active']) || !empty($editingProduct['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Visible en boutique</label>
                    </div>
                </div>
                <div class="col-12 d-grid gap-2">
                    <button class="btn btn-dark" type="submit" name="save_product" value="1">
                        <?= $editingProduct ? 'Mettre a jour l article' : 'Ajouter l article' ?>
                    </button>
                    <?php if ($editingProduct): ?>
                        <a class="btn btn-outline-dark" href="<?= e(base_url('admin/products.php')) ?>">Annuler</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="glass-panel">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="eyebrow mb-1">Catalogue</p>
                    <h3 class="h4 mb-0">Liste des articles</h3>
                </div>
                <span class="small-muted"><?= count($products) ?> article<?= count($products) > 1 ? 's' : '' ?></span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Categorie</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Etat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <strong><?= e($product['name']) ?></strong>
                                    <div class="small-muted"><?= e($product['sku']) ?></div>
                                </td>
                                <td><?= e($product['category_name']) ?></td>
                                <td><?= format_price((float) ($product['promo_price'] ?: $product['price'])) ?></td>
                                <td>
                                    <?= (int) $product['stock'] ?>
                                    <?php if ((int) $product['stock'] <= LOW_STOCK_THRESHOLD): ?>
                                        <span class="badge text-bg-warning ms-1">Faible</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge text-bg-<?= !empty($product['is_active']) ? 'success' : 'secondary' ?>">
                                        <?= !empty($product['is_active']) ? 'Actif' : 'Masque' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a class="btn btn-outline-dark btn-sm" href="<?= e(base_url('admin/products.php?edit=' . (int) $product['id'])) ?>">Modifier</a>
                                        <form method="post">
                                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                            <button class="btn btn-outline-danger btn-sm" type="submit" name="delete_product" value="1">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes_bottom.php'; ?>
