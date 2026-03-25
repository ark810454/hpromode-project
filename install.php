<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$host = env_value('DB_HOST', 'localhost');
$port = (int) env_value('DB_PORT', 3306);
$dbname = env_value('DB_NAME', 'hpromode');
$username = env_value('DB_USER', 'root');
$password = env_value('DB_PASS', '');
$message = '';
$messageType = 'info';
$isProduction = env_value('APP_ENV', 'local') === 'production';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isProduction) {
        $message = 'Installation desactivee en production pour proteger la base.';
        $messageType = 'warning';
    } else {
        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};charset=utf8mb4",
                $username,
                $password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                )
            );

            $sqlFile = __DIR__ . '/sql/hpromode.sql';
            $sqlContent = file_get_contents($sqlFile);

            if ($sqlContent === false) {
                throw new Exception('Impossible de lire le fichier SQL.');
            }

            $statements = preg_split('/;\s*[\r\n]+/', $sqlContent);

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if ($statement === '') {
                    continue;
                }

                $pdo->exec($statement);
            }

            $message = 'Base de donnees initialisee avec succes. Vous pouvez maintenant ouvrir la boutique HPROMODE.';
            $messageType = 'success';
        } catch (Exception $exception) {
            $message = 'Installation impossible : ' . $exception->getMessage();
            $messageType = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation | HPROMODE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('css/style.css')) ?>">
</head>
<body class="admin-login-body">
    <section class="auth-section">
        <div class="container">
            <div class="auth-card auth-card-wide glass-panel">
                <p class="eyebrow text-center">Installation locale</p>
                <h1 class="section-title text-center mb-4">Reinitialiser la base HPROMODE</h1>
                <?php if ($message !== ''): ?>
                    <div class="alert alert-<?= e($messageType) ?> luxury-alert"><?= e($message) ?></div>
                <?php endif; ?>
                <p class="small-muted">
                    Cette action recharge completement la base <strong><?= e($dbname) ?></strong> a partir de
                    <code>sql/hpromode.sql</code>. Utilisez-la si vous voyez des erreurs du type
                    "Unknown column" ou si votre schema local est ancien.
                </p>
                <form method="post" class="mt-4 d-grid gap-3">
                    <button class="btn btn-dark btn-lg" type="submit" <?= $isProduction ? 'disabled' : '' ?>>Installer / Reparer la base</button>
                    <a class="btn btn-outline-secondary btn-lg" href="<?= e(base_url('index.php')) ?>">Retour au site</a>
                </form>
            </div>
        </div>
    </section>
</body>
</html>
