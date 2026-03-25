<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_admin()) {
    redirect('admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '' || $password === '') {
        flash('danger', 'Veuillez saisir vos identifiants administrateur.');
        redirect('admin/login.php');
    }

    $statement = $pdo->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
    $statement->execute(array($email));
    $admin = $statement->fetch();

    if (!$admin || !password_verify($password, $admin['password'])) {
        flash('danger', 'Identifiants administrateur invalides.');
        redirect('admin/login.php');
    }

    $_SESSION['admin'] = $admin;
    flash('success', 'Bienvenue dans l administration HPROMODE.');
    redirect('admin/index.php');
}

$adminStyleVersion = file_exists(ROOT_PATH . '/assets/css/style.css') ? filemtime(ROOT_PATH . '/assets/css/style.css') : time();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion admin | <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('css/style.css?v=' . $adminStyleVersion)) ?>">
</head>
<body class="admin-login-body">
    <section class="auth-section">
        <div class="container">
            <div class="auth-card glass-panel">
                <p class="eyebrow text-center">Acces securise</p>
                <h1 class="section-title text-center mb-4">Connexion administrateur</h1>
                <p class="small-muted text-center mb-4">Accedez au dashboard pour gerer les articles, categories, commandes et clients.</p>
                <?php if ($message = flash('success')): ?>
                    <div class="alert alert-success luxury-alert"><?= e($message) ?></div>
                <?php endif; ?>
                <?php if ($message = flash('danger')): ?>
                    <div class="alert alert-danger luxury-alert"><?= e($message) ?></div>
                <?php endif; ?>
                <form method="post" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="admin@hpromode.test" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" placeholder="admin123" required>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-dark btn-lg" type="submit">Acceder au dashboard</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</body>
</html>
