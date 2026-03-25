from pathlib import Path
root = Path('/mnt/data/hpromode_project')
files = {}

files['config/db.php'] = r'''<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'hpromode';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Connexion à la base de données impossible : ' . $e->getMessage());
}
?>'''

files['includes/functions.php'] = r'''<?php
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

function is_admin(): bool {
    return !empty($_SESSION['admin']);
}

function require_login(): void {
    if (!is_logged_in()) {
        flash('danger', 'Veuillez vous connecter pour continuer.');
        redirect('/hpromode/login.php');
    }
}

function require_admin(): void {
    if (!is_admin()) {
        flash('danger', 'Accès administrateur requis.');
        redirect('/hpromode/admin/login.php');
    }
}

function cart_items_count(): int {
    $count = 0;
    foreach ($_SESSION['cart'] ?? [] as $item) {
        $count += (int)$item['quantity'];
    }
    return $count;
}

function get_cart_total(PDO $pdo): float {
    $total = 0;
    foreach ($_SESSION['cart'] ?? [] as $productId => $item) {
        $stmt = $pdo->prepare('SELECT price, promo_price FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if ($product) {
            $price = $product['promo_price'] !== null ? (float)$product['promo_price'] : (float)$product['price'];
            $total += $price * (int)$item['quantity'];
        }
    }
    return $total;
}

function format_price(float $price): string {
    return number_format($price, 2, ',', ' ') . ' $';
}

function get_setting_badge(string $status): string {
    return match($status) {
        'paid', 'livrée', 'delivered' => 'success',
        'pending', 'en attente', 'processing', 'en préparation' => 'warning',
        'failed', 'cancelled', 'rupture', 'expédiée' => 'danger',
        default => 'secondary',
    };
}
?>'''

files['includes/header.php'] = r'''<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'HPROMODE';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | HPROMODE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/hpromode/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top luxury-nav shadow-sm">
  <div class="container">
    <a class="navbar-brand brand-mark" href="/hpromode/index.php">HPROMODE</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/hpromode/index.php">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="/hpromode/shop.php">Boutique</a></li>
        <li class="nav-item"><a class="nav-link" href="/hpromode/shop.php?category=robes">Robes</a></li>
        <li class="nav-item"><a class="nav-link" href="/hpromode/shop.php?category=costumes">Costumes</a></li>
        <li class="nav-item"><a class="nav-link" href="/hpromode/shop.php?category=accessoires">Accessoires</a></li>
      </ul>
      <div class="d-flex align-items-center gap-3">
        <a class="nav-link" href="/hpromode/cart.php">Panier <span class="badge text-bg-light"><?= cart_items_count() ?></span></a>
        <?php if (is_logged_in()): ?>
            <a class="nav-link" href="/hpromode/profile.php">Mon compte</a>
            <a class="btn btn-outline-light btn-sm" href="/hpromode/logout.php">Déconnexion</a>
        <?php else: ?>
            <a class="btn btn-outline-light btn-sm" href="/hpromode/login.php">Connexion</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<main>
<div class="container py-3">
    <?php if ($message = flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= e($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($message = flash('danger')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= e($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
</div>
'''

files['includes/footer.php'] = r'''</main>
<footer class="footer-luxury mt-5">
  <div class="container py-5">
    <div class="row g-4">
      <div class="col-md-4">
        <h5 class="brand-mark">HPROMODE</h5>
        <p>Élégance Redéfinie. Mode premium inspirée d’un univers bordeaux, bleu royal et rose poudré.</p>
      </div>
      <div class="col-md-4">
        <h6>Navigation</h6>
        <ul class="list-unstyled small">
          <li><a href="/hpromode/index.php">Accueil</a></li>
          <li><a href="/hpromode/shop.php">Boutique</a></li>
          <li><a href="/hpromode/profile.php">Mon compte</a></li>
          <li><a href="/hpromode/admin/login.php">Admin</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h6>Contact</h6>
        <p class="small mb-1">Kinshasa · support@hpromode.test</p>
        <p class="small mb-0">WhatsApp : +243 000 000 000</p>
      </div>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/hpromode/assets/js/app.js"></script>
</body>
</html>
'''

files['assets/css/style.css'] = r'''@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;700&family=Inter:wght@400;500;600;700&display=swap');
:root {
  --bordeaux: #6e1028;
  --royal: #204a96;
  --powder: #efd6de;
  --gold: #d4af37;
  --cream: #faf6f1;
  --ink: #191417;
}
body {
  font-family: 'Inter', sans-serif;
  background: linear-gradient(180deg, #fffdfd 0%, #f8f1f4 45%, #fff 100%);
  color: var(--ink);
}
.brand-mark, h1, h2, h3, h4, .hero-title {
  font-family: 'Cormorant Garamond', serif;
  letter-spacing: .04em;
}
.luxury-nav {
  background: linear-gradient(90deg, rgba(110,16,40,.96), rgba(32,74,150,.88));
  backdrop-filter: blur(10px);
}
.navbar-brand.brand-mark { font-size: 2rem; }
.hero {
  min-height: 76vh;
  border-radius: 1.5rem;
  overflow: hidden;
  position: relative;
  background:
    linear-gradient(120deg, rgba(110,16,40,.80), rgba(239,214,222,.20), rgba(32,74,150,.55)),
    url('/hpromode/assets/images/hero.svg') center/cover no-repeat;
  box-shadow: 0 30px 60px rgba(74, 24, 39, .18);
}
.hero::after {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 20% 30%, rgba(255,255,255,.45), transparent 20%),
              radial-gradient(circle at 80% 20%, rgba(255,221,199,.35), transparent 18%),
              radial-gradient(circle at 55% 85%, rgba(255,255,255,.18), transparent 20%);
}
.hero-content { position: relative; z-index: 2; }
.hero-title { font-size: clamp(3rem, 8vw, 5.5rem); color: white; }
.hero-subtitle { color: #f8e9ef; max-width: 640px; }
.btn-luxury {
  background: linear-gradient(90deg, var(--gold), #e7c766);
  color: #2a1a0c;
  border: none;
  font-weight: 700;
  box-shadow: 0 14px 30px rgba(212, 175, 55, .3);
}
.btn-luxury:hover { color: #2a1a0c; transform: translateY(-1px); }
.section-title { font-size: 2.5rem; }
.glass-card, .product-card, .stat-card {
  background: rgba(255,255,255,.8);
  border: 1px solid rgba(255,255,255,.6);
  box-shadow: 0 20px 45px rgba(63, 28, 46, .09);
  backdrop-filter: blur(10px);
  border-radius: 1rem;
}
.product-card { transition: transform .25s ease, box-shadow .25s ease; height: 100%; }
.product-card:hover { transform: translateY(-6px); box-shadow: 0 24px 55px rgba(63, 28, 46, .14); }
.product-image {
  height: 270px;
  object-fit: cover;
  border-top-left-radius: 1rem;
  border-top-right-radius: 1rem;
  background: linear-gradient(135deg, #f6d8df, #c0d1f2);
}
.category-chip {
  background: rgba(110,16,40,.08);
  color: var(--bordeaux);
  border: 1px solid rgba(110,16,40,.12);
  border-radius: 50rem;
  padding: .35rem .8rem;
  display: inline-block;
  font-size: .85rem;
}
.luxury-banner {
  background: linear-gradient(90deg, rgba(110,16,40,.95), rgba(32,74,150,.86));
  color: white;
  border-radius: 1.2rem;
}
.footer-luxury {
  background: linear-gradient(90deg, #150d10, #241926);
  color: #f5ecf0;
}
.footer-luxury a { color: #f0d7df; text-decoration: none; }
.auth-card { max-width: 520px; margin: 0 auto; }
.form-control:focus, .form-select:focus {
  border-color: rgba(110,16,40,.5);
  box-shadow: 0 0 0 .25rem rgba(110,16,40,.10);
}
.table thead th { background: #f6ecf0; }
.admin-sidebar {
  min-height: 100vh;
  background: linear-gradient(180deg, #230f17, #142b56);
}
.admin-sidebar a { color: #f7e7ec; text-decoration: none; display: block; padding: .75rem 1rem; border-radius: .75rem; }
.admin-sidebar a:hover, .admin-sidebar a.active { background: rgba(255,255,255,.1); }
.placeholder-box {
  width: 100%; height: 100%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700;
  background: linear-gradient(135deg, var(--bordeaux), var(--royal));
}
.small-muted { color: #7b6b72; }
.badge-soft { background: #f6e7ec; color: var(--bordeaux); }
.marble-bg { background: linear-gradient(135deg, #fff, #f3e5ea 35%, #e7eefb); }
'''

files['assets/js/app.js'] = r'''document.querySelectorAll('[data-confirm]').forEach((element) => {
  element.addEventListener('click', (e) => {
    if (!confirm(element.dataset.confirm || 'Confirmer cette action ?')) {
      e.preventDefault();
    }
  });
});
'''

files['index.php'] = r'''<?php
$pageTitle = 'Accueil';
require_once __DIR__ . '/includes/header.php';
$featured = $pdo->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC LIMIT 6")->fetchAll();
?>
<section class="container pb-5">
  <div class="hero p-4 p-md-5 d-flex align-items-center">
    <div class="hero-content col-lg-7">
      <span class="category-chip bg-white text-dark">HPROMODE · Élégance Redéfinie</span>
      <h1 class="hero-title mt-3">Mode de luxe inspirée d’une vitrine premium.</h1>
      <p class="hero-subtitle fs-5">Une boutique chic aux tons bordeaux, bleu royal et rose poudré, avec une ambiance cinématographique et sophistiquée.</p>
      <div class="d-flex flex-wrap gap-3 mt-4">
        <a class="btn btn-luxury btn-lg px-4" href="shop.php">Acheter maintenant</a>
        <a class="btn btn-outline-light btn-lg px-4" href="#featured">Voir la collection</a>
      </div>
    </div>
  </div>
</section>

<section class="container py-5" id="featured">
  <div class="d-flex justify-content-between align-items-end mb-4">
    <div>
      <p class="small text-uppercase small-muted mb-1">Collection sélectionnée</p>
      <h2 class="section-title mb-0">Produits vedettes</h2>
    </div>
    <a href="shop.php" class="btn btn-outline-dark">Voir tout</a>
  </div>
  <div class="row g-4">
    <?php foreach ($featured as $product): ?>
      <div class="col-md-6 col-lg-4">
        <div class="product-card overflow-hidden">
          <img class="product-image w-100" src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
          <div class="p-4">
            <span class="category-chip"><?= e($product['category_name']) ?></span>
            <h3 class="h4 mt-3"><?= e($product['name']) ?></h3>
            <p class="small-muted"><?= e(substr($product['description'], 0, 90)) ?>...</p>
            <div class="d-flex justify-content-between align-items-center">
              <strong><?= format_price((float)($product['promo_price'] ?? $product['price'])) ?></strong>
              <a href="product-details.php?id=<?= (int)$product['id'] ?>" class="btn btn-dark">Détails</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="container py-3">
  <div class="luxury-banner p-4 p-md-5">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <h2 class="section-title text-white">L’univers HPROMODE</h2>
        <p class="mb-0">Robes, costumes et accessoires présentés avec une mise en scène boutique haut de gamme. Le site est pensé pour une expérience simple, élégante et prête pour le paiement.</p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a href="register.php" class="btn btn-light btn-lg">Créer un compte</a>
      </div>
    </div>
  </div>
</section>

<section class="container py-5">
  <div class="row g-4">
    <div class="col-md-4"><div class="glass-card p-4 h-100"><h4>Palette luxe</h4><p class="mb-0">Bordeaux, bleu royal, rose poudré et touches dorées pour refléter la marque.</p></div></div>
    <div class="col-md-4"><div class="glass-card p-4 h-100"><h4>Paiement simplifié</h4><p class="mb-0">Paiement à la livraison, virement, Mobile Money et carte en mode simulation.</p></div></div>
    <div class="col-md-4"><div class="glass-card p-4 h-100"><h4>Administration</h4><p class="mb-0">Ajout de produits, gestion des commandes, paiements, clients et promotions.</p></div></div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>'''

files['shop.php'] = r'''<?php
$pageTitle = 'Boutique';
require_once __DIR__ . '/includes/header.php';

$conditions = [];
$params = [];

$search = trim($_GET['search'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$sort = $_GET['sort'] ?? 'newest';

$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id = p.category_id";

if ($search !== '') {
    $conditions[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($categorySlug !== '') {
    $conditions[] = 'c.slug = ?';
    $params[] = $categorySlug;
}
if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= match($sort) {
    'price_asc' => ' ORDER BY COALESCE(p.promo_price, p.price) ASC',
    'price_desc' => ' ORDER BY COALESCE(p.promo_price, p.price) DESC',
    'popular' => ' ORDER BY p.stock DESC',
    default => ' ORDER BY p.id DESC'
};
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
?>
<section class="container py-5">
  <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
    <div>
      <p class="small text-uppercase small-muted mb-1">Boutique premium</p>
      <h1 class="section-title mb-0">Collection HPROMODE</h1>
    </div>
    <form class="row g-2" method="get">
      <div class="col-md-4"><input class="form-control" name="search" placeholder="Rechercher..." value="<?= e($search) ?>"></div>
      <div class="col-md-3">
        <select class="form-select" name="category">
          <option value="">Toutes catégories</option>
          <?php foreach ($categories as $category): ?>
            <option value="<?= e($category['slug']) ?>" <?= $categorySlug === $category['slug'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select class="form-select" name="sort">
          <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Nouveautés</option>
          <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
          <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Prix décroissant</option>
          <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Popularité</option>
        </select>
      </div>
      <div class="col-md-2"><button class="btn btn-dark w-100">Filtrer</button></div>
    </form>
  </div>

  <div class="row g-4">
    <?php foreach ($products as $product): ?>
      <div class="col-md-6 col-xl-4">
        <div class="product-card overflow-hidden">
          <img src="<?= e($product['image']) ?>" class="product-image w-100" alt="<?= e($product['name']) ?>">
          <div class="p-4">
            <div class="d-flex justify-content-between mb-2">
              <span class="category-chip"><?= e($product['category_name']) ?></span>
              <span class="badge text-bg-<?= $product['stock'] > 0 ? 'success' : 'danger' ?>"><?= $product['stock'] > 0 ? 'En stock' : 'Rupture' ?></span>
            </div>
            <h3 class="h4"><?= e($product['name']) ?></h3>
            <p class="small-muted"><?= e(substr($product['description'], 0, 100)) ?>...</p>
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <?php if ($product['promo_price']): ?><span class="text-decoration-line-through small-muted me-2"><?= format_price((float)$product['price']) ?></span><?php endif; ?>
                <strong><?= format_price((float)($product['promo_price'] ?? $product['price'])) ?></strong>
              </div>
              <a href="product-details.php?id=<?= (int)$product['id'] ?>" class="btn btn-dark">Voir</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>'''

files['product-details.php'] = r'''<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    flash('danger', 'Produit introuvable.');
    redirect('/hpromode/shop.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    if ($quantity > (int)$product['stock']) {
        flash('danger', 'Quantité demandée supérieure au stock disponible.');
    } else {
        $_SESSION['cart'][$id] = [
            'quantity' => $quantity,
            'name' => $product['name'],
            'image' => $product['image'],
        ];
        flash('success', 'Produit ajouté au panier.');
        redirect('/hpromode/cart.php');
    }
}
$pageTitle = $product['name'];
require_once __DIR__ . '/includes/header.php';
$relatedStmt = $pdo->prepare('SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 3');
$relatedStmt->execute([$product['category_id'], $id]);
$related = $relatedStmt->fetchAll();
?>
<section class="container py-5">
  <div class="row g-5 align-items-start">
    <div class="col-lg-6">
      <img src="<?= e($product['image']) ?>" class="img-fluid rounded-4 shadow-sm marble-bg" alt="<?= e($product['name']) ?>">
    </div>
    <div class="col-lg-6">
      <span class="category-chip"><?= e($product['category_name']) ?></span>
      <h1 class="display-5 mt-3"><?= e($product['name']) ?></h1>
      <p class="lead"><?= e($product['description']) ?></p>
      <div class="fs-4 mb-3">
        <?php if ($product['promo_price']): ?><span class="text-decoration-line-through small-muted me-2"><?= format_price((float)$product['price']) ?></span><?php endif; ?>
        <strong><?= format_price((float)($product['promo_price'] ?? $product['price'])) ?></strong>
      </div>
      <p><strong>Stock :</strong> <?= (int)$product['stock'] ?> unités</p>
      <form method="post" class="glass-card p-4">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Quantité</label>
            <input type="number" min="1" max="<?= (int)$product['stock'] ?>" value="1" name="quantity" class="form-control">
          </div>
          <div class="col-md-8 d-flex align-items-end gap-2">
            <button class="btn btn-luxury px-4">Ajouter au panier</button>
            <a href="cart.php" class="btn btn-outline-dark">Voir le panier</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>

<section class="container pb-5">
  <h2 class="section-title mb-4">Produits similaires</h2>
  <div class="row g-4">
    <?php foreach ($related as $item): ?>
      <div class="col-md-4">
        <div class="product-card overflow-hidden">
          <img src="<?= e($item['image']) ?>" class="product-image w-100" alt="<?= e($item['name']) ?>">
          <div class="p-3">
            <h3 class="h5"><?= e($item['name']) ?></h3>
            <div class="d-flex justify-content-between align-items-center"><strong><?= format_price((float)($item['promo_price'] ?? $item['price'])) ?></strong><a class="btn btn-dark btn-sm" href="product-details.php?id=<?= (int)$item['id'] ?>">Voir</a></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>'''

files['cart.php'] = r'''<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        foreach ($_POST['quantities'] ?? [] as $productId => $quantity) {
            $quantity = max(1, (int)$quantity);
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['quantity'] = $quantity;
            }
        }
        flash('success', 'Panier mis à jour.');
    }
    if (isset($_POST['remove'])) {
        $productId = (int)$_POST['product_id'];
        unset($_SESSION['cart'][$productId]);
        flash('success', 'Produit retiré du panier.');
    }
    redirect('/hpromode/cart.php');
}

$pageTitle = 'Panier';
require_once __DIR__ . '/includes/header.php';
$items = [];
foreach ($_SESSION['cart'] ?? [] as $productId => $cartItem) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    if ($product = $stmt->fetch()) {
        $items[] = ['product' => $product, 'quantity' => $cartItem['quantity']];
    }
}
$total = get_cart_total($pdo);
?>
<section class="container py-5">
  <h1 class="section-title mb-4">Votre panier</h1>
  <?php if (!$items): ?>
    <div class="glass-card p-5 text-center">
      <h3>Votre panier est vide</h3>
      <p class="small-muted">Explorez la collection HPROMODE pour ajouter des pièces premium.</p>
      <a href="shop.php" class="btn btn-dark">Continuer les achats</a>
    </div>
  <?php else: ?>
    <form method="post">
      <div class="table-responsive glass-card p-3">
        <table class="table align-middle mb-0">
          <thead><tr><th>Produit</th><th>Prix</th><th>Quantité</th><th>Sous-total</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($items as $item): $product = $item['product']; $qty = (int)$item['quantity']; $price = (float)($product['promo_price'] ?? $product['price']); ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <img src="<?= e($product['image']) ?>" alt="" width="70" class="rounded-3">
                  <div><strong><?= e($product['name']) ?></strong><div class="small-muted">Stock : <?= (int)$product['stock'] ?></div></div>
                </div>
              </td>
              <td><?= format_price($price) ?></td>
              <td><input type="number" min="1" name="quantities[<?= (int)$product['id'] ?>]" value="<?= $qty ?>" class="form-control" style="max-width:100px"></td>
              <td><?= format_price($price * $qty) ?></td>
              <td>
                <button class="btn btn-outline-danger btn-sm" name="remove" value="1">Supprimer</button>
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-4">
        <button class="btn btn-outline-dark" name="update" value="1">Mettre à jour</button>
        <div class="glass-card p-4 text-end">
          <div class="fs-4 mb-3">Total : <strong><?= format_price($total) ?></strong></div>
          <a href="checkout.php" class="btn btn-luxury">Passer à la commande</a>
        </div>
      </div>
    </form>
  <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>'''

files['checkout.php'] = r'''<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

if (empty($_SESSION['cart'])) {
    flash('danger', 'Votre panier est vide.');
    redirect('/hpromode/shop.php');
}

$total = get_cart_total($pdo);
$deliveryFee = 10.00;
$grandTotal = $total + $deliveryFee;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['first_name', 'last_name', 'phone', 'email', 'address', 'city', 'country', 'delivery_method', 'payment_method'];
    foreach ($required as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            flash('danger', 'Veuillez remplir tous les champs du formulaire.');
            redirect('/hpromode/checkout.php');
        }
    }

    $pdo->beginTransaction();
    try {
        $orderNumber = 'HPR-' . date('Ymd') . '-' . random_int(1000, 9999);
        $stmt = $pdo->prepare('INSERT INTO orders (user_id, order_number, first_name, last_name, phone, email, address, city, country, total_amount, delivery_fee, delivery_method, payment_method, payment_status, delivery_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            current_user()['id'], $orderNumber,
            trim($_POST['first_name']), trim($_POST['last_name']), trim($_POST['phone']), trim($_POST['email']), trim($_POST['address']), trim($_POST['city']), trim($_POST['country']),
            $total, $deliveryFee, trim($_POST['delivery_method']), trim($_POST['payment_method']), 'pending', 'en attente'
        ]);
        $orderId = (int)$pdo->lastInsertId();

        foreach ($_SESSION['cart'] as $productId => $item) {
            $productStmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $productStmt->execute([$productId]);
            $product = $productStmt->fetch();
            if (!$product || $product['stock'] < $item['quantity']) {
                throw new Exception('Stock insuffisant pour ' . ($product['name'] ?? 'un produit'));
            }
            $price = (float)($product['promo_price'] ?? $product['price']);
            $insertItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
            $insertItem->execute([$orderId, $productId, $item['quantity'], $price]);
            $updateStock = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
            $updateStock->execute([$item['quantity'], $productId]);
        }

        $paymentReference = 'PAY-' . strtoupper(bin2hex(random_bytes(4)));
        $pay = $pdo->prepare('INSERT INTO payments (order_id, payment_method, amount, status, transaction_reference) VALUES (?, ?, ?, ?, ?)');
        $pay->execute([$orderId, trim($_POST['payment_method']), $grandTotal, 'pending', $paymentReference]);

        $del = $pdo->prepare('INSERT INTO deliveries (order_id, delivery_method, fee, status) VALUES (?, ?, ?, ?)');
        $del->execute([$orderId, trim($_POST['delivery_method']), $deliveryFee, 'en attente']);

        $pdo->commit();
        $_SESSION['cart'] = [];
        flash('success', 'Commande validée avec succès. Numéro : ' . $orderNumber);
        redirect('/hpromode/profile.php');
    } catch (Throwable $e) {
        $pdo->rollBack();
        flash('danger', 'Erreur lors de la commande : ' . $e->getMessage());
        redirect('/hpromode/checkout.php');
    }
}

$pageTitle = 'Commande';
require_once __DIR__ . '/includes/header.php';
$user = current_user();
?>
<section class="container py-5">
  <h1 class="section-title mb-4">Finaliser votre commande</h1>
  <div class="row g-4">
    <div class="col-lg-7">
      <form method="post" class="glass-card p-4">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Prénom</label><input class="form-control" name="first_name" value="<?= e($user['first_name'] ?? '') ?>" required></div>
          <div class="col-md-6"><label class="form-label">Nom</label><input class="form-control" name="last_name" value="<?= e($user['last_name'] ?? '') ?>" required></div>
          <div class="col-md-6"><label class="form-label">Téléphone</label><input class="form-control" name="phone" required></div>
          <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= e($user['email'] ?? '') ?>" required></div>
          <div class="col-12"><label class="form-label">Adresse</label><input class="form-control" name="address" required></div>
          <div class="col-md-4"><label class="form-label">Ville</label><input class="form-control" name="city" required></div>
          <div class="col-md-4"><label class="form-label">Pays</label><input class="form-control" name="country" value="RDC" required></div>
          <div class="col-md-4"><label class="form-label">Livraison</label><select class="form-select" name="delivery_method"><option>Livraison standard</option><option>Livraison express</option><option>Retrait boutique</option></select></div>
          <div class="col-md-6"><label class="form-label">Paiement</label><select class="form-select" name="payment_method"><option>Paiement à la livraison</option><option>Virement bancaire</option><option>Mobile Money</option><option>Carte bancaire (simulation)</option></select></div>
          <div class="col-12"><button class="btn btn-luxury btn-lg">Valider la commande</button></div>
        </div>
      </form>
    </div>
    <div class="col-lg-5">
      <div class="glass-card p-4">
        <h3 class="h4">Résumé</h3>
        <ul class="list-group list-group-flush mb-3">
          <?php foreach ($_SESSION['cart'] as $productId => $item):
              $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
              $stmt->execute([$productId]);
              $product = $stmt->fetch();
              if (!$product) continue;
              $price = (float)($product['promo_price'] ?? $product['price']);
          ?>
          <li class="list-group-item d-flex justify-content-between bg-transparent px-0"><span><?= e($product['name']) ?> × <?= (int)$item['quantity'] ?></span><strong><?= format_price($price * (int)$item['quantity']) ?></strong></li>
          <?php endforeach; ?>
          <li class="list-group-item d-flex justify-content-between bg-transparent px-0"><span>Produits</span><strong><?= format_price($total) ?></strong></li>
          <li class="list-group-item d-flex justify-content-between bg-transparent px-0"><span>Livraison</span><strong><?= format_price($deliveryFee) ?></strong></li>
          <li class="list-group-item d-flex justify-content-between bg-transparent px-0 fs-5"><span>Total final</span><strong><?= format_price($grandTotal) ?></strong></li>
        </ul>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>'''

files['login.php'] = r'''<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        flash('success', 'Connexion réussie.');
        redirect('/hpromode/profile.php');
    }
    flash('danger', 'Identifiants invalides.');
    redirect('/hpromode/login.php');
}
$pageTitle = 'Connexion';
require_once __DIR__ . '/includes/header.php';
?>
<section class="container py-5">
  <div class="auth-card glass-card p-4 p-md-5">
    <h1 class="section-title mb-4 text-center">Connexion</h1>
    <form method="post" class="row g-3">
      <div class="col-12"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
      <div class="col-12"><label class="form-label">Mot de passe</label><input type="password" name="password" class="form-control" required></div>
      <div class="col-12"><button class="btn btn-dark w-100">Se connecter</button></div>
      <div class="col-12 text-center small-muted">Pas encore de compte ? <a href="register.php">Créer un compte</a></div>
    </form>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>'''

files['register.php'] = r'''<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$firstName || !$lastName || !$email || !$password) {
        flash('danger', 'Tous les champs sont obligatoires.');
        redirect('/hpromode/register.php');
    }
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        flash('danger', 'Cet email existe déjà.');
        redirect('/hpromode/register.php');
    }
    $insert = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)');
    $insert->execute([$firstName, $lastName, $email, password_hash($password, PASSWORD_DEFAULT)]);
    flash('success', 'Compte créé avec succès. Connectez-vous.');
    redirect('/hpromode/login.php');
}
$pageTitle = 'Inscription';
require_once __DIR__ . '/includes/header.php';
?>
<section class="container py-5">
  <div class="auth-card glass-card p-4 p-md-5">
    <h1 class="section-title mb-4 text-center">Créer un compte</h1>
    <form method="post" class="row g-3">
      <div class="col-md-6"><label class="form-label">Prénom</label><input name="first_name" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label">Nom</label><input name="last_name" class="form-control" required></div>
      <div class="col-12"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
      <div class="col-12"><label class="form-label">Mot de passe</label><input type="password" name="password" class="form-control" required></div>
      <div class="col-12"><button class="btn btn-luxury w-100">S'inscrire</button></div>
    </form>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>'''

files['profile.php'] = r'''<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'Mon compte';
require_once __DIR__ . '/includes/header.php';
$user = current_user();
$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();
?>
<section class="container py-5">
  <div class="row g-4">
    <div class="col-lg-4">
      <div class="glass-card p-4">
        <h1 class="section-title h2">Mon profil</h1>
        <p class="mb-1"><strong>Nom :</strong> <?= e($user['first_name'] . ' ' . $user['last_name']) ?></p>
        <p class="mb-0"><strong>Email :</strong> <?= e($user['email']) ?></p>
      </div>
    </div>
    <div class="col-lg-8">
      <div class="glass-card p-4">
        <h2 class="h3 mb-4">Historique des commandes</h2>
        <?php if (!$orders): ?>
          <p class="mb-0">Aucune commande pour le moment.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>N° commande</th><th>Montant</th><th>Paiement</th><th>Livraison</th><th>Date</th></tr></thead>
              <tbody>
              <?php foreach ($orders as $order): ?>
                <tr>
                  <td><?= e($order['order_number']) ?></td>
                  <td><?= format_price((float)$order['total_amount'] + (float)$order['delivery_fee']) ?></td>
                  <td><span class="badge text-bg-<?= get_setting_badge($order['payment_status']) ?>"><?= e($order['payment_status']) ?></span></td>
                  <td><span class="badge text-bg-<?= get_setting_badge($order['delivery_status']) ?>"><?= e($order['delivery_status']) ?></span></td>
                  <td><?= e($order['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>'''

files['logout.php'] = r'''<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
unset($_SESSION['user']);
flash('success', 'Vous êtes déconnecté.');
redirect('/hpromode/index.php');
?>'''

files['admin/login.php'] = r'''<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ?');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin'] = $admin;
        flash('success', 'Bienvenue dans l’administration.');
        redirect('/hpromode/admin/index.php');
    }
    flash('danger', 'Identifiants administrateur invalides.');
    redirect('/hpromode/admin/login.php');
}
$pageTitle = 'Admin Connexion';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="container py-5">
  <div class="auth-card glass-card p-4 p-md-5">
    <h1 class="section-title mb-4 text-center">Administration</h1>
    <form method="post" class="row g-3">
      <div class="col-12"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="admin@hpromode.test" required></div>
      <div class="col-12"><label class="form-label">Mot de passe</label><input type="password" name="password" class="form-control" placeholder="admin123" required></div>
      <div class="col-12"><button class="btn btn-dark w-100">Se connecter</button></div>
    </form>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>'''

files['admin/includes_top.php'] = r'''<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'Admin') ?> | HPROMODE</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/hpromode/assets/css/style.css">
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <aside class="col-lg-2 admin-sidebar p-3">
      <h3 class="brand-mark text-white mb-4">HPROMODE</h3>
      <nav class="d-grid gap-2">
        <a href="/hpromode/admin/index.php">Dashboard</a>
        <a href="/hpromode/admin/products.php">Produits</a>
        <a href="/hpromode/admin/orders.php">Commandes</a>
        <a href="/hpromode/index.php">Voir le site</a>
        <a href="/hpromode/admin/logout.php">Déconnexion</a>
      </nav>
    </aside>
    <section class="col-lg-10 p-4">
      <?php if ($message = flash('success')): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
      <?php if ($message = flash('danger')): ?><div class="alert alert-danger"><?= e($message) ?></div><?php endif; ?>
'''

files['admin/includes_bottom.php'] = r'''    </section>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
'''

files['admin/index.php'] = r'''<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes_top.php';
$stats = [
    'products' => (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'orders' => (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'users' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'sales' => (float)$pdo->query('SELECT COALESCE(SUM(total_amount + delivery_fee),0) FROM orders')->fetchColumn(),
];
$recentOrders = $pdo->query('SELECT order_number, first_name, last_name, total_amount, payment_status, delivery_status, created_at FROM orders ORDER BY id DESC LIMIT 8')->fetchAll();
?>
<h1 class="section-title mb-4">Tableau de bord</h1>
<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="stat-card p-4"><div class="small-muted">Produits</div><div class="display-6"><?= $stats['products'] ?></div></div></div>
  <div class="col-md-3"><div class="stat-card p-4"><div class="small-muted">Commandes</div><div class="display-6"><?= $stats['orders'] ?></div></div></div>
  <div class="col-md-3"><div class="stat-card p-4"><div class="small-muted">Clients</div><div class="display-6"><?= $stats['users'] ?></div></div></div>
  <div class="col-md-3"><div class="stat-card p-4"><div class="small-muted">Ventes</div><div class="display-6"><?= format_price($stats['sales']) ?></div></div></div>
</div>
<div class="glass-card p-4">
  <h2 class="h4 mb-3">Dernières commandes</h2>
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>N°</th><th>Client</th><th>Montant</th><th>Paiement</th><th>Livraison</th><th>Date</th></tr></thead>
      <tbody>
      <?php foreach ($recentOrders as $order): ?>
        <tr>
          <td><?= e($order['order_number']) ?></td>
          <td><?= e($order['first_name'] . ' ' . $order['last_name']) ?></td>
          <td><?= format_price((float)$order['total_amount']) ?></td>
          <td><span class="badge text-bg-<?= get_setting_badge($order['payment_status']) ?>"><?= e($order['payment_status']) ?></span></td>
          <td><span class="badge text-bg-<?= get_setting_badge($order['delivery_status']) ?>"><?= e($order['delivery_status']) ?></span></td>
          <td><?= e($order['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/includes_bottom.php'; ?>'''

files['admin/products.php'] = r'''<?php
$pageTitle = 'Produits';
require_once __DIR__ . '/includes_top.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $promoPrice = trim($_POST['promo_price'] ?? '') !== '' ? (float)$_POST['promo_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $image = trim($_POST['image'] ?? '');

    if (isset($_POST['create'])) {
        $stmt = $pdo->prepare('INSERT INTO products (category_id, name, description, price, promo_price, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$categoryId, $name, $description, $price, $promoPrice, $stock, $image]);
        flash('success', 'Produit ajouté.');
    }

    if (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Produit supprimé.');
    }

    redirect('/hpromode/admin/products.php');
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.id DESC')->fetchAll();
?>
<h1 class="section-title mb-4">Gestion des produits</h1>
<div class="row g-4">
  <div class="col-lg-5">
    <div class="glass-card p-4">
      <h2 class="h4 mb-3">Ajouter un produit</h2>
      <form method="post" class="row g-3">
        <div class="col-12"><label class="form-label">Nom</label><input name="name" class="form-control" required></div>
        <div class="col-12"><label class="form-label">Catégorie</label><select name="category_id" class="form-select"><?php foreach($categories as $category): ?><option value="<?= (int)$category['id'] ?>"><?= e($category['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4" required></textarea></div>
        <div class="col-md-4"><label class="form-label">Prix</label><input type="number" step="0.01" name="price" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Promo</label><input type="number" step="0.01" name="promo_price" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Stock</label><input type="number" name="stock" class="form-control" required></div>
        <div class="col-12"><label class="form-label">Image (chemin)</label><input name="image" class="form-control" value="/hpromode/assets/images/product-default.svg" required></div>
        <div class="col-12"><button class="btn btn-dark" name="create" value="1">Ajouter</button></div>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="glass-card p-4">
      <h2 class="h4 mb-3">Liste des produits</h2>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Produit</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($products as $product): ?>
            <tr>
              <td><?= e($product['name']) ?></td>
              <td><?= e($product['category_name']) ?></td>
              <td><?= format_price((float)($product['promo_price'] ?? $product['price'])) ?></td>
              <td><?= (int)$product['stock'] ?></td>
              <td>
                <form method="post" onsubmit="return confirm('Supprimer ce produit ?')">
                  <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                  <button class="btn btn-outline-danger btn-sm" name="delete" value="1">Supprimer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes_bottom.php'; ?>'''

files['admin/orders.php'] = r'''<?php
$pageTitle = 'Commandes';
require_once __DIR__ . '/includes_top.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $paymentStatus = trim($_POST['payment_status'] ?? 'pending');
    $deliveryStatus = trim($_POST['delivery_status'] ?? 'en attente');
    $stmt = $pdo->prepare('UPDATE orders SET payment_status = ?, delivery_status = ? WHERE id = ?');
    $stmt->execute([$paymentStatus, $deliveryStatus, $id]);
    flash('success', 'Commande mise à jour.');
    redirect('/hpromode/admin/orders.php');
}

$orders = $pdo->query('SELECT * FROM orders ORDER BY id DESC')->fetchAll();
?>
<h1 class="section-title mb-4">Gestion des commandes</h1>
<div class="glass-card p-4">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>N°</th><th>Client</th><th>Total</th><th>Paiement</th><th>Livraison</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($orders as $order): ?>
        <tr>
          <td><?= e($order['order_number']) ?></td>
          <td><?= e($order['first_name'] . ' ' . $order['last_name']) ?></td>
          <td><?= format_price((float)$order['total_amount'] + (float)$order['delivery_fee']) ?></td>
          <td><?= e($order['payment_method']) ?><br><span class="badge text-bg-<?= get_setting_badge($order['payment_status']) ?>"><?= e($order['payment_status']) ?></span></td>
          <td><span class="badge text-bg-<?= get_setting_badge($order['delivery_status']) ?>"><?= e($order['delivery_status']) ?></span></td>
          <td>
            <form method="post" class="d-grid gap-2">
              <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
              <select class="form-select form-select-sm" name="payment_status">
                <?php foreach (['pending','paid','failed'] as $status): ?><option value="<?= $status ?>" <?= $order['payment_status'] === $status ? 'selected' : '' ?>><?= $status ?></option><?php endforeach; ?>
              </select>
              <select class="form-select form-select-sm" name="delivery_status">
                <?php foreach (['en attente','en préparation','expédiée','livrée'] as $status): ?><option value="<?= $status ?>" <?= $order['delivery_status'] === $status ? 'selected' : '' ?>><?= $status ?></option><?php endforeach; ?>
              </select>
              <button class="btn btn-dark btn-sm">Mettre à jour</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/includes_bottom.php'; ?>'''

files['admin/logout.php'] = r'''<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
unset($_SESSION['admin']);
flash('success', 'Session administrateur fermée.');
redirect('/hpromode/admin/login.php');
?>'''

files['sql/hpromode.sql'] = r'''CREATE DATABASE IF NOT EXISTS hpromode CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hpromode;

DROP TABLE IF EXISTS deliveries;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS promotions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    promo_price DECIMAL(10,2) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(60) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(180) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    delivery_method VARCHAR(120) NOT NULL,
    payment_method VARCHAR(120) NOT NULL,
    payment_status VARCHAR(50) NOT NULL DEFAULT 'pending',
    delivery_status VARCHAR(50) NOT NULL DEFAULT 'en attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method VARCHAR(120) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    transaction_reference VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    delivery_method VARCHAR(120) NOT NULL,
    fee DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'en attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_deliveries_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    code VARCHAR(100) DEFAULT NULL,
    discount_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    discount_value DECIMAL(10,2) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins (name, email, password) VALUES
('Super Admin', 'admin@hpromode.test', '$2y$10$L1A4Y2QK6HI0s96M/7hYouR8P9s8gjZspGDZCBoBPXaGNsq4CPGK2');

INSERT INTO categories (name, slug) VALUES
('Robes', 'robes'),
('Costumes', 'costumes'),
('Sacs', 'sacs'),
('Montres', 'montres'),
('Lunettes', 'lunettes'),
('Bijoux', 'bijoux'),
('Accessoires', 'accessoires');

INSERT INTO products (category_id, name, description, price, promo_price, stock, image) VALUES
(1, 'Robe Bordeaux Prestige', 'Robe de soirée élégante avec finition satinée et silhouette glamour.', 180.00, 150.00, 8, '/hpromode/assets/images/robe-bordeaux.svg'),
(1, 'Robe Rose Poudré Élégance', 'Longue robe raffinée, esprit tapis rouge et luxe contemporain.', 210.00, NULL, 6, '/hpromode/assets/images/robe-rose.svg'),
(2, 'Costume Bleu Royal Signature', 'Costume trois pièces premium pour cérémonies et rendez-vous d’affaires.', 250.00, 220.00, 5, '/hpromode/assets/images/costume-bleu.svg'),
(2, 'Costume Rosé Modern Chic', 'Costume au ton poudré pour une allure élégante et audacieuse.', 240.00, NULL, 4, '/hpromode/assets/images/costume-rose.svg'),
(3, 'Sac Bordeaux Luxe', 'Sac structuré haut de gamme avec détails dorés.', 120.00, 95.00, 12, '/hpromode/assets/images/sac-bordeaux.svg'),
(3, 'Sac Bleu Royal Iconic', 'Sac premium compact pour compléter un look sophistiqué.', 130.00, NULL, 10, '/hpromode/assets/images/sac-bleu.svg'),
(4, 'Montre Élégance Dorée', 'Montre premium minimaliste avec bracelet métal et cadran clair.', 160.00, 140.00, 9, '/hpromode/assets/images/montre.svg'),
(5, 'Lunettes Glamour', 'Lunettes chics aux lignes raffinées pour une allure de marque.', 75.00, NULL, 15, '/hpromode/assets/images/lunettes.svg'),
(6, 'Bracelet Lumière', 'Bracelet délicat aux accents lumineux pour soirées et cérémonies.', 90.00, NULL, 14, '/hpromode/assets/images/bracelet.svg');

INSERT INTO promotions (title, code, discount_type, discount_value, is_active) VALUES
('Promo lancement', 'HPRO10', 'percent', 10, 1);
'''

files['README.txt'] = r'''HPROMODE - Projet e-commerce PHP/MySQL

INSTALLATION RAPIDE
1. Copier le dossier "hpromode_project" dans le dossier htdocs de XAMPP.
2. Renommer le dossier en "hpromode".
3. Créer une base MySQL en important le fichier sql/hpromode.sql dans phpMyAdmin.
4. Vérifier dans config/db.php :
   - host = localhost
   - dbname = hpromode
   - username = root
   - password = ''
5. Lancer Apache + MySQL.
6. Ouvrir : http://localhost/hpromode/

COMPTES DE TEST
Admin
- email : admin@hpromode.test
- mot de passe : admin123

Utilisateur
- créer un compte via register.php

REMARQUES
- Le paiement est en mode simple/simulation.
- Le design s’inspire d’un univers luxe : bordeaux, bleu royal, rose poudré.
- Pour des paiements réels, il faudra intégrer une passerelle par la suite.
'''

# Simple SVG assets
svgs = {
'assets/images/hero.svg': '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 900"><defs><linearGradient id="g" x1="0" x2="1"><stop stop-color="#6e1028"/><stop offset="0.5" stop-color="#eecfd9"/><stop offset="1" stop-color="#204a96"/></linearGradient></defs><rect width="1600" height="900" fill="url(#g)"/><g opacity="0.22"><circle cx="220" cy="180" r="140" fill="#fff"/><circle cx="1350" cy="170" r="120" fill="#ffe6d9"/><circle cx="1210" cy="680" r="160" fill="#fff"/></g><g fill="#fff" opacity="0.17"><rect x="80" y="80" width="220" height="740" rx="20"/><rect x="1320" y="80" width="200" height="740" rx="20"/></g><text x="800" y="420" text-anchor="middle" font-size="110" fill="#ffffff" font-family="Georgia, serif">HPROMODE</text><text x="800" y="505" text-anchor="middle" font-size="46" fill="#fff4f7" font-family="Georgia, serif">Élégance Redéfinie</text></svg>''',
'assets/images/product-default.svg': '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800"><defs><linearGradient id="g" x1="0" x2="1"><stop stop-color="#6e1028"/><stop offset="1" stop-color="#204a96"/></linearGradient></defs><rect width="800" height="800" fill="url(#g)"/><text x="400" y="390" text-anchor="middle" fill="#fff" font-size="72" font-family="Georgia, serif">HPROMODE</text><text x="400" y="460" text-anchor="middle" fill="#f4dbe3" font-size="36" font-family="Arial">LUXE</text></svg>''',
'assets/images/robe-bordeaux.svg': '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800"><rect width="800" height="800" fill="#f8e5ea"/><path d="M300 170c30-65 170-65 200 0l30 110c18 70 45 138 80 199l46 81H145l45-81c35-61 62-129 80-199l30-110z" fill="#7b102e"/><circle cx="400" cy="120" r="58" fill="#2a1a1f"/></svg>''',
'assets/images/robe-rose.svg': '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800"><rect width="800" height="800" fill="#fff5f8"/><path d="M300 170c30-65 170-65 200 0l26 90c25 85 60 164 102 237l33 58H139l33-58c42-73 77-152 102-237l26-90z" fill="#d8a8b8"/><circle cx="400" cy="120" r="58" fill="#2a1a1f"/></svg>''',
'assets/images/costume-bleu.svg': '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800"><rect width="800" height="800" fill="#e9eef9"/><circle cx="400" cy="130" r="60" fill="#2a1a1f"/><path d="M250 230h300l60 350H190l60-350z" fill="#7397da"/><path d="M345 230h110l25 130-80 220-80-220z" fill="#f6f2f0"/><rect x="370" y="245" width="60" height="185" fill="#503533"/></svg>''',
'assets/images/costume-rose.svg': '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800"><rect width="800" height="800" fill="#f8edf0"/><circle cx="400" cy="130" r="60" fill="#2a1a1f"/><path d="M250 230h300l60 350H190l60-350z" fill="#e4bcc6"/><path d="M345 230h110l25 130-80 220-80-220z" fill="#faf7f7"/><rect x="370" y="245" width="60" height="185" fill="#d4b1bd"/></svg>''',
'assets/images/sac-bordeaux.svg': '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800"><rect width="800" height="800" fill="#f6ebef"/><rect x="190" y="280" width="420" height="290" rx="30" fill="#86203b"/><path d="M290 280c0-62 42-100 110-100s110 38 110 100" fill="none" stroke="#b98d54" stroke-width="24"/><rect x="370" y="385" width="60" height="42" rx="8" fill="#d4af37"/></svg>''',
'assets/images/sac-bleu.svg': '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800"><rect width="800" height="800" fill="#eef2fb"/><rect x="190" y="280" width="420" height="290" rx="30" fill="#4f77c8"/><path d="M290 280c0-62 42-100 110-100s110 38 110 100" fill="none" stroke="#d4af37" stroke-width="24"/><rect x="370" y="385" width="60" height="42" rx="8" fill="#e3c161"/></svg>''',
'assets/images/montre.svg': '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800"><rect width="800" height="800" fill="#faf6f1"/><rect x="335" y="60" width="130" height="170" rx="24" fill="#d6b876"/><rect x="335" y="570" width="130" height="170" rx="24" fill="#d6b876"/><circle cx="400" cy="400" r="165" fill="#fefefe" stroke="#d6b876" stroke-width="28"/><line x1="400" y1="400" x2="400" y2="300" stroke="#705327" stroke-width="16"/><line x1="400" y1="400" x2="470" y2="440" stroke="#705327" stroke-width="16"/></svg>''',
'assets/images/lunettes.svg': '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800"><rect width="800" height="800" fill="#f2e6ea"/><circle cx="280" cy="400" r="110" fill="#4d3240" opacity="0.88"/><circle cx="520" cy="400" r="110" fill="#4d3240" opacity="0.88"/><rect x="350" y="380" width="100" height="26" rx="13" fill="#c8a768"/><path d="M170 360c-48-14-90-9-125 10" fill="none" stroke="#c8a768" stroke-width="16"/><path d="M630 360c48-14 90-9 125 10" fill="none" stroke="#c8a768" stroke-width="16"/></svg>''',
'assets/images/bracelet.svg': '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800"><rect width="800" height="800" fill="#fff6f4"/><ellipse cx="400" cy="420" rx="250" ry="120" fill="none" stroke="#d8b06a" stroke-width="22"/><g fill="#f9fafc" stroke="#d8b06a" stroke-width="10"><circle cx="180" cy="430" r="36"/><circle cx="260" cy="360" r="42"/><circle cx="360" cy="325" r="48"/><circle cx="465" cy="335" r="44"/><circle cx="555" cy="380" r="38"/><circle cx="620" cy="450" r="34"/></g></svg>'''
}
files.update(svgs)

for rel, content in files.items():
    path = root / rel
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(content, encoding='utf-8')

print('generated', len(files), 'files')
