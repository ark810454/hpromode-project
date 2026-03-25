<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('profile.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim(isset($_POST['first_name']) ? $_POST['first_name'] : '');
    $lastName = trim(isset($_POST['last_name']) ? $_POST['last_name'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $phone = trim(isset($_POST['phone']) ? $_POST['phone'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($firstName === '' || $lastName === '' || $email === '' || $password === '' || $confirmPassword === '') {
        flash('danger', 'Tous les champs marqués sont obligatoires.');
        redirect('register.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('danger', 'Adresse email invalide.');
        redirect('register.php');
    }

    if (strlen($password) < 8) {
        flash('danger', 'Le mot de passe doit contenir au moins 8 caractères.');
        redirect('register.php');
    }

    if ($password !== $confirmPassword) {
        flash('danger', 'Les mots de passe ne correspondent pas.');
        redirect('register.php');
    }

    $statement = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $statement->execute([$email]);
    if ($statement->fetch()) {
        flash('danger', 'Cet email est déjà utilisé.');
        redirect('register.php');
    }

    $insert = $pdo->prepare(
        'INSERT INTO users (first_name, last_name, email, phone, password, city, country) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $insert->execute([
        $firstName,
        $lastName,
        $email,
        $phone,
        password_hash($password, PASSWORD_DEFAULT),
        '',
        'Nigeria',
    ]);

    flash('success', 'Compte créé avec succès. Connectez-vous pour continuer.');
    redirect('login.php');
}

$pageTitle = 'Inscription';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-card auth-card-wide glass-panel">
            <p class="eyebrow text-center">Maison HPROMODE</p>
            <h1 class="section-title text-center mb-4">Créer un compte</h1>
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nom</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <div class="col-12 d-grid">
                    <button class="btn btn-luxury btn-lg" type="submit">Créer mon compte</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
