<?php
$pageTitle = 'Accueil';
require_once __DIR__ . '/includes/header.php';

$featuredProducts = $pdo->query(
    "SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.is_active = 1
     ORDER BY p.is_featured DESC, p.created_at DESC
     LIMIT 6"
)->fetchAll();

$newArrivals = $pdo->query(
    "SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.is_active = 1
     ORDER BY p.is_new DESC, p.created_at DESC
     LIMIT 4"
)->fetchAll();

$promotion = get_active_promotion($pdo);
?>

<section class="home-hero home-hero-structured">
    <div class="home-hero-media">
        <img src="<?= e(asset_url('images/hero-hpromode.jpeg')) ?>" alt="Campagne HPROMODE">
    </div>
    <div class="home-hero-overlay"></div>
    <div class="container home-hero-shell">
        <div class="hero-structured-grid">
            <div class="home-hero-content reveal-up">
                <p class="eyebrow text-gold">Maison HPROMODE</p>
                <h1>HPROMODE</h1>
                <p class="home-hero-tagline">Elegance Redefinie</p>
                <p class="home-hero-copy">
                    Une maison de mode premium qui melange desir editorial, silhouettes fortes
                    et experience d'achat plus claire, plus chic et plus internationale.
                </p>
                <div class="home-hero-actions">
                    <a class="btn btn-luxury btn-lg" href="<?= e(base_url('shop.php')) ?>">Decouvrir la collection</a>
                    <a class="btn btn-outline-light btn-lg" href="#featured">Acheter maintenant</a>
                </div>
            </div>
            <div class="hero-side-panel reveal-up">
                <div class="hero-side-panel-inner">
                    <span class="eyebrow text-gold">Collection du moment</span>
                    <h3>Une organisation plus nette, comme une vraie vitrine de marque.</h3>
                    <p>
                        Grands visuels, blocs bien alignes, parcours lisible et ambiance luxe plus mature.
                    </p>
                    <a class="hero-side-link" href="<?= e(base_url('shop.php')) ?>">Entrer dans la boutique</a>
                </div>
            </div>
        </div>

        <div class="hero-pillars reveal-up">
            <article class="hero-pillar">
                <span>Nouvelle collection</span>
                <strong>Femme, homme et accessoires premium</strong>
                <p>Une lecture plus simple et plus forte de l'offre HPROMODE.</p>
            </article>
            <article class="hero-pillar">
                <span>Luxe editorial</span>
                <strong>Bordeaux, noir, creme et accents dores</strong>
                <p>Une direction artistique plus calme, plus propre et plus desirante.</p>
            </article>
            <article class="hero-pillar">
                <span>Parcours premium</span>
                <strong>Boutique, produit et lookbook mieux organises</strong>
                <p>Le site devient plus facile a lire et beaucoup plus credible visuellement.</p>
            </article>
            <article class="hero-pillar">
                <span>Signature</span>
                <strong>Location mariage et accessoires valorises</strong>
                <p>Des univers complementaires integres dans une seule narration de marque.</p>
            </article>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <div class="split-intro reveal-up" id="univers">
            <div class="split-intro-copy">
                <p class="eyebrow">A propos de HPROMODE</p>
                <h2 class="section-title">Une maison pensee comme un melange entre campagne mode, boutique et lookbook.</h2>
                <p>
                    HPROMODE prend une direction plus structuree: un hero fort, des blocs d'information
                    mieux hierarchises, puis des sections editoriales qui respirent. L'objectif est simple:
                    faire ressentir une vraie marque de mode internationale.
                </p>
                <ul class="story-list">
                    <li>Selection femme avec robes, ceremonie et location mariage</li>
                    <li>Selection homme avec costumes et tailoring premium</li>
                    <li>Accessoires premium presentes comme des objets de desir</li>
                </ul>
            </div>
            <div class="split-intro-media">
                <img src="<?= e(asset_url('images/wedding-rental.svg')) ?>" alt="Univers HPROMODE">
            </div>
        </div>
    </div>
</section>

<section class="section-block section-alt">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Selections</p>
                <h2 class="section-title">Des univers mieux organises, plus lisibles et plus desirables.</h2>
            </div>
        </div>
        <div class="editorial-grid">
            <a class="editorial-card editorial-card-feature reveal-up" href="<?= e(base_url('shop.php?category=robes')) ?>">
                <div class="editorial-card-media">
                    <img src="<?= e(asset_url('images/robe-rose.svg')) ?>" alt="Selection femme HPROMODE">
                </div>
                <div class="editorial-card-body">
                    <span class="eyebrow">Selection femme</span>
                    <h3>Robes, ceremonie et allure editoriale</h3>
                    <p>Une mise en scene plus nette pour les silhouettes feminines, la ceremonie et la location mariage.</p>
                </div>
            </a>
            <a class="editorial-card reveal-up" href="<?= e(base_url('shop.php?category=costumes')) ?>">
                <div class="editorial-card-media">
                    <img src="<?= e(asset_url('images/costume-bleu.svg')) ?>" alt="Selection homme HPROMODE">
                </div>
                <div class="editorial-card-body">
                    <span class="eyebrow">Selection homme</span>
                    <h3>Costumes et allure signature</h3>
                    <p>Des lignes plus masculines et plus structurees dans un esprit luxe contemporain.</p>
                </div>
            </a>
            <a class="editorial-card reveal-up" href="<?= e(base_url('shop.php?category=sacs')) ?>">
                <div class="editorial-card-media">
                    <img src="<?= e(asset_url('images/sac-bordeaux.svg')) ?>" alt="Accessoires premium HPROMODE">
                </div>
                <div class="editorial-card-body">
                    <span class="eyebrow">Accessoires premium</span>
                    <h3>Sacs, bijoux et finitions desirables</h3>
                    <p>Les accessoires prennent enfin une vraie place dans l'identite visuelle de la marque.</p>
                </div>
            </a>
        </div>
    </div>
</section>

<section class="section-block" id="featured">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Produits vedettes</p>
                <h2 class="section-title">Des pieces fortes presentees avec plus d'air et de clarte.</h2>
            </div>
            <a class="btn btn-outline-dark" href="<?= e(base_url('shop.php')) ?>">Voir toute la boutique</a>
        </div>
        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
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
                            <div class="product-card-footer">
                                <div>
                                    <?php if (!empty($product['promo_price'])): ?>
                                        <span class="old-price"><?= format_price((float) $product['price']) ?></span>
                                    <?php endif; ?>
                                    <strong><?= format_price((float) ($product['promo_price'] ? $product['promo_price'] : $product['price'])) ?></strong>
                                </div>
                                <span class="product-card-cta">Voir le produit</span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-block section-alt" id="lookbook">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Lookbook</p>
                <h2 class="section-title">Une presentation plus magazine, sans perdre la fonction boutique.</h2>
            </div>
        </div>
        <div class="lookbook-grid">
            <article class="lookbook-frame lookbook-frame-large reveal-up">
                <img src="<?= e(asset_url('images/hero.svg')) ?>" alt="Lookbook HPROMODE">
                <div class="lookbook-frame-copy">
                    <span class="eyebrow text-gold">Collection du moment</span>
                    <h3>Une silhouette globale plus lisible, plus forte, plus premium</h3>
                </div>
            </article>
            <article class="lookbook-frame reveal-up">
                <img src="<?= e(asset_url('images/robe-bordeaux.svg')) ?>" alt="Lookbook robes">
                <div class="lookbook-frame-copy">
                    <span class="eyebrow">Robes</span>
                    <h3>Feminite couture</h3>
                </div>
            </article>
            <article class="lookbook-frame reveal-up">
                <img src="<?= e(asset_url('images/costume-bleu.svg')) ?>" alt="Lookbook costumes">
                <div class="lookbook-frame-copy">
                    <span class="eyebrow">Costumes</span>
                    <h3>Tailoring iconique</h3>
                </div>
            </article>
            <article class="lookbook-frame reveal-up">
                <img src="<?= e(asset_url('images/sac-bleu.svg')) ?>" alt="Lookbook accessoires">
                <div class="lookbook-frame-copy">
                    <span class="eyebrow">Accessoires</span>
                    <h3>Objets de desir</h3>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-6">
                <div class="quote-panel reveal-up">
                    <p class="eyebrow">Nouveautes</p>
                    <h2 class="section-title">Le catalogue recent garde une lecture simple et elegante.</h2>
                    <div class="product-grid product-grid-compact mt-4">
                        <?php foreach ($newArrivals as $product): ?>
                            <article class="product-card">
                                <a class="product-card-link" href="<?= e(base_url('product-details.php?id=' . (int) $product['id'])) ?>">
                                    <div class="product-card-media">
                                        <img src="<?= e($product['main_image']) ?>" alt="<?= e($product['name']) ?>">
                                    </div>
                                    <div class="product-card-body">
                                        <h3><?= e($product['name']) ?></h3>
                                        <div class="product-card-footer">
                                            <strong><?= format_price((float) ($product['promo_price'] ? $product['promo_price'] : $product['price'])) ?></strong>
                                            <span class="product-card-cta">Decouvrir</span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="quote-panel quote-panel-dark reveal-up">
                    <p class="eyebrow text-gold">Le moment HPROMODE</p>
                    <h2 class="section-title text-white">Une maison pensee pour inspirer desir, confiance et exclusivite.</h2>
                    <p class="text-white-50 mb-4">
                        <?= $promotion ? e($promotion['title']) . ' avec le code ' . e($promotion['code']) . '.' : 'Des offres discretes, elegantes et coherentes avec une image de marque premium.' ?>
                    </p>
                    <div class="home-note-stack">
                        <div>
                            <span>Collection limitee</span>
                            <strong>Location robe de mariage et accessoires valorises</strong>
                        </div>
                        <div>
                            <span>Experience</span>
                            <strong>Une navigation plus ordonnee, plus credible et plus haut de gamme</strong>
                        </div>
                    </div>
                    <a class="btn btn-luxury mt-4" href="<?= e(base_url('shop.php?category=robes#location-mariage')) ?>">Voir la location mariage</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
