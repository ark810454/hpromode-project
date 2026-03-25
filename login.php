<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('profile.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '' || $password === '') {
        flash('danger', 'Veuillez saisir votre email et votre mot de passe.');
        redirect('login.php');
    }

    $statement = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $statement->execute([$email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        flash('danger', 'Identifiants invalides.');
        redirect('login.php');
    }

    $_SESSION['user'] = $user;
    merge_cart_for_user($pdo, (int) $user['id']);

    flash('success', 'Connexion réussie.');
    redirect('profile.php');
}

$pageTitle = 'Connexion';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-card glass-panel">
            <p class="eyebrow text-center">Espace client</p>
            <h1 class="section-title text-center mb-4">Connexion</h1>
            <form method="post" class="row g-3">
                <div class="col-12">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-12 d-grid">
                    <button class="btn btn-dark btn-lg" type="submit">Se connecter</button>
                </div>
            </form>
            <p class="small-muted text-center mt-4 mb-0">
                Nouveau client ? <a href="<?= e(base_url('register.php')) ?>">Créer un compte</a>
            </p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
