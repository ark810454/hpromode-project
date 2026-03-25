<?php
$pageTitle = 'Promotions';
require_once __DIR__ . '/includes_top.php';

$editingPromotion = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_promotion'])) {
        $promotionId = (int) (isset($_POST['promotion_id']) ? $_POST['promotion_id'] : 0);
        $title = trim(isset($_POST['title']) ? $_POST['title'] : '');
        $code = trim(isset($_POST['code']) ? $_POST['code'] : '');
        $bannerText = trim(isset($_POST['banner_text']) ? $_POST['banner_text'] : '');
        $discountType = trim(isset($_POST['discount_type']) ? $_POST['discount_type'] : 'percent');
        $discountValue = (float) (isset($_POST['discount_value']) ? $_POST['discount_value'] : 0);
        $startDate = trim(isset($_POST['start_date']) ? $_POST['start_date'] : '');
        $startDate = $startDate !== '' ? $startDate : null;
        $endDate = trim(isset($_POST['end_date']) ? $_POST['end_date'] : '');
        $endDate = $endDate !== '' ? $endDate : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($title === '' || $code === '' || $discountValue <= 0) {
            flash('danger', 'Titre, code et valeur de réduction sont obligatoires.');
            redirect('admin/promotions.php' . ($promotionId ? '?edit=' . $promotionId : ''));
        }

        if ($promotionId > 0) {
            $statement = $pdo->prepare(
                'UPDATE promotions SET title = ?, code = ?, banner_text = ?, discount_type = ?, discount_value = ?, start_date = ?, end_date = ?, is_active = ? WHERE id = ?'
            );
            $statement->execute([$title, strtoupper($code), $bannerText, $discountType, $discountValue, $startDate, $endDate, $isActive, $promotionId]);
            flash('success', 'Promotion mise à jour.');
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO promotions (title, code, banner_text, discount_type, discount_value, start_date, end_date, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $statement->execute([$title, strtoupper($code), $bannerText, $discountType, $discountValue, $startDate, $endDate, $isActive]);
            flash('success', 'Promotion créée.');
        }

        redirect('admin/promotions.php');
    }

    if (isset($_POST['delete_promotion'])) {
        $promotionId = (int) (isset($_POST['promotion_id']) ? $_POST['promotion_id'] : 0);
        $statement = $pdo->prepare('DELETE FROM promotions WHERE id = ?');
        $statement->execute([$promotionId]);
        flash('success', 'Promotion supprimée.');
        redirect('admin/promotions.php');
    }
}

if (isset($_GET['edit'])) {
    $statement = $pdo->prepare('SELECT * FROM promotions WHERE id = ?');
    $statement->execute([(int) $_GET['edit']]);
    $editingPromotion = $statement->fetch() ?: null;
}

$promotions = $pdo->query('SELECT * FROM promotions ORDER BY id DESC')->fetchAll();
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="glass-panel">
            <h3 class="h4 mb-4"><?= $editingPromotion ? 'Modifier la promotion' : 'Nouvelle promotion' ?></h3>
            <form method="post" class="row g-3">
                <input type="hidden" name="promotion_id" value="<?= (int) array_value($editingPromotion, 'id', 0) ?>">
                <div class="col-12">
                    <label class="form-label">Titre</label>
                    <input type="text" name="title" class="form-control" value="<?= e(array_value($editingPromotion, 'title', '')) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Code promo</label>
                    <input type="text" name="code" class="form-control" value="<?= e(array_value($editingPromotion, 'code', '')) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Bannière promo</label>
                    <textarea name="banner_text" class="form-control" rows="3"><?= e(array_value($editingPromotion, 'banner_text', '')) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Type</label>
                    <select name="discount_type" class="form-select">
                        <option value="percent" <?= array_value($editingPromotion, 'discount_type', '') === 'percent' ? 'selected' : '' ?>>Pourcentage</option>
                        <option value="fixed" <?= array_value($editingPromotion, 'discount_type', '') === 'fixed' ? 'selected' : '' ?>>Montant fixe</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Valeur</label>
                    <input type="number" step="0.01" name="discount_value" class="form-control" value="<?= e((string) array_value($editingPromotion, 'discount_value', '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Début</label>
                    <input type="date" name="start_date" class="form-control" value="<?= e(array_value($editingPromotion, 'start_date', '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fin</label>
                    <input type="date" name="end_date" class="form-control" value="<?= e(array_value($editingPromotion, 'end_date', '')) ?>">
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="promo_active" <?= !isset($editingPromotion['is_active']) || !empty($editingPromotion['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="promo_active">Promotion active</label>
                    </div>
                </div>
                <div class="col-12 d-grid gap-2">
                    <button class="btn btn-dark" type="submit" name="save_promotion" value="1">
                        <?= $editingPromotion ? 'Mettre à jour' : 'Créer la promotion' ?>
                    </button>
                    <?php if ($editingPromotion): ?>
                        <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/promotions.php')) ?>">Annuler</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="glass-panel">
            <h3 class="h4 mb-4">Campagnes promotionnelles</h3>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Code</th>
                            <th>Réduction</th>
                            <th>Période</th>
                            <th>État</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($promotions as $promotion): ?>
                            <tr>
                                <td><?= e($promotion['title']) ?></td>
                                <td><?= e($promotion['code']) ?></td>
                                <td>
                                    <?= $promotion['discount_type'] === 'percent'
                                        ? e((string) ((float) $promotion['discount_value'])) . '%'
                                        : format_price((float) $promotion['discount_value']) ?>
                                </td>
                                <td>
                                    <div class="small-muted"><?= e($promotion['start_date'] ?: 'Immédiat') ?></div>
                                    <div class="small-muted"><?= e($promotion['end_date'] ?: 'Sans fin') ?></div>
                                </td>
                                <td><span class="badge text-bg-<?= !empty($promotion['is_active']) ? 'success' : 'secondary' ?>"><?= !empty($promotion['is_active']) ? 'Active' : 'Inactive' ?></span></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a class="btn btn-outline-dark btn-sm" href="<?= e(base_url('admin/promotions.php?edit=' . (int) $promotion['id'])) ?>">Modifier</a>
                                        <form method="post">
                                            <input type="hidden" name="promotion_id" value="<?= (int) $promotion['id'] ?>">
                                            <button class="btn btn-outline-danger btn-sm" type="submit" name="delete_promotion" value="1">Supprimer</button>
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
