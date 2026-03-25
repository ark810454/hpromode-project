<?php
$pageTitle = 'Categories';
require_once __DIR__ . '/includes_top.php';

$editingCategory = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $slug = trim(isset($_POST['slug']) ? $_POST['slug'] : '');
    $description = trim(isset($_POST['description']) ? $_POST['description'] : '');

    if (isset($_POST['save_category'])) {
        if ($name === '') {
            flash('danger', 'Le nom de la categorie est obligatoire.');
            redirect('admin/categories.php');
        }

        $slug = $slug !== '' ? generate_slug($slug) : generate_slug($name);
        $categoryId = (int) (isset($_POST['category_id']) ? $_POST['category_id'] : 0);

        if ($categoryId > 0) {
            $statement = $pdo->prepare('UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?');
            $statement->execute(array($name, $slug, $description, $categoryId));
            flash('success', 'Categorie mise a jour.');
        } else {
            $statement = $pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)');
            $statement->execute(array($name, $slug, $description));
            flash('success', 'Categorie ajoutee.');
        }

        redirect('admin/categories.php');
    }

    if (isset($_POST['delete_category'])) {
        $categoryId = (int) (isset($_POST['category_id']) ? $_POST['category_id'] : 0);
        $statement = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $statement->execute(array($categoryId));
        flash('success', 'Categorie supprimee.');
        redirect('admin/categories.php');
    }
}

if (isset($_GET['edit'])) {
    $statement = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $statement->execute(array((int) $_GET['edit']));
    $editingCategory = $statement->fetch() ?: null;
}

$categories = $pdo->query(
    'SELECT c.*, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id
     ORDER BY c.name ASC'
)->fetchAll();
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="glass-panel">
            <p class="eyebrow"><?= $editingCategory ? 'Modifier' : 'Nouvelle categorie' ?></p>
            <h3 class="h4 mb-4"><?= $editingCategory ? 'Mettre a jour la categorie' : 'Ajouter une categorie' ?></h3>
            <form method="post" class="row g-3">
                <input type="hidden" name="category_id" value="<?= (int) array_value($editingCategory, 'id', 0) ?>">
                <div class="col-12">
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="form-control" value="<?= e(array_value($editingCategory, 'name', '')) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?= e(array_value($editingCategory, 'slug', '')) ?>" placeholder="Genere automatiquement si vide">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= e(array_value($editingCategory, 'description', '')) ?></textarea>
                </div>
                <div class="col-12 d-grid gap-2">
                    <button class="btn btn-dark" type="submit" name="save_category" value="1">
                        <?= $editingCategory ? 'Mettre a jour la categorie' : 'Ajouter la categorie' ?>
                    </button>
                    <?php if ($editingCategory): ?>
                        <a class="btn btn-outline-dark" href="<?= e(base_url('admin/categories.php')) ?>">Annuler</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="glass-panel">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <p class="eyebrow mb-1">Organisation du catalogue</p>
                    <h3 class="h4 mb-0">Liste des categories</h3>
                </div>
                <span class="small-muted"><?= count($categories) ?> categorie<?= count($categories) > 1 ? 's' : '' ?></span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Slug</th>
                            <th>Produits</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td>
                                    <strong><?= e($category['name']) ?></strong>
                                    <div class="small-muted"><?= e($category['description']) ?></div>
                                </td>
                                <td><?= e($category['slug']) ?></td>
                                <td><?= (int) $category['product_count'] ?></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a class="btn btn-outline-dark btn-sm" href="<?= e(base_url('admin/categories.php?edit=' . (int) $category['id'])) ?>">Modifier</a>
                                        <form method="post">
                                            <input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>">
                                            <button class="btn btn-outline-danger btn-sm" type="submit" name="delete_category" value="1">Supprimer</button>
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
