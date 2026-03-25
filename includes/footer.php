</main>

<footer class="footer-luxury">
    <div class="container">
        <div class="footer-newsletter">
            <div>
                <p class="eyebrow text-gold">Newsletter privee</p>
                <h2 class="section-title text-white">Recevez les editions, capsules et offres exclusives HPROMODE.</h2>
            </div>
            <form class="newsletter-form" data-newsletter-form>
                <input type="email" class="form-control form-control-lg" placeholder="Votre adresse e-mail" required>
                <button class="btn btn-luxury btn-lg" type="submit">S'inscrire</button>
            </form>
        </div>

        <div class="footer-grid">
            <div class="footer-brand">
                <p class="eyebrow text-gold mb-3">HPROMODE</p>
                <h3 class="brand-mark">Elegance Redefinie</h3>
                <p class="footer-copy">
                    Une maison de mode digitale inspiree des grandes signatures du pret-a-porter,
                    entre sobriete editoriale, desir mode et experience d'achat premium.
                </p>
            </div>
            <div>
                <h6>Collection</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= e(base_url('shop.php?category=robes')) ?>">Robes</a></li>
                    <li><a href="<?= e(base_url('shop.php?category=costumes')) ?>">Costumes</a></li>
                    <li><a href="<?= e(base_url('shop.php?category=sacs')) ?>">Sacs</a></li>
                    <li><a href="<?= e(base_url('shop.php?category=bijoux')) ?>">Bijoux</a></li>
                </ul>
            </div>
            <div>
                <h6>Maison</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= e(base_url('index.php')) ?>#univers">Notre univers</a></li>
                    <li><a href="<?= e(base_url('index.php')) ?>#lookbook">Lookbook</a></li>
                    <li><a href="<?= e(base_url('shop.php')) ?>">Boutique</a></li>
                    <li><a href="<?= e(base_url('profile.php')) ?>">Mon compte</a></li>
                </ul>
            </div>
            <div>
                <h6>Service client</h6>
                <ul class="list-unstyled footer-links">
                    <li><span><?= e(APP_SUPPORT_EMAIL) ?></span></li>
                    <li><span><?= e(APP_SUPPORT_PHONE) ?></span></li>
                    <li><span>Livraison premium internationale</span></li>
                    <li><span>Retours et accompagnement personnalise</span></li>
                </ul>
            </div>
            <div>
                <h6>Suivre HPROMODE</h6>
                <div class="social-row">
                    <a href="#">Instagram</a>
                    <a href="#">Pinterest</a>
                    <a href="#">TikTok</a>
                    <a href="#">Facebook</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> HPROMODE. Tous droits reserves.</span>
            <span>Maison de mode premium, responsive et pensee pour une experience d'achat internationale.</span>
        </div>
    </div>
</footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php $scriptVersion = file_exists(ROOT_PATH . '/assets/js/app.js') ? filemtime(ROOT_PATH . '/assets/js/app.js') : time(); ?>
<script src="<?= e(asset_url('js/app.js?v=' . $scriptVersion)) ?>"></script>
</body>
</html>
