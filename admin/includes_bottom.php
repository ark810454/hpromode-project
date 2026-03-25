        </main>
    </div>

    <?php $adminScriptVersion = file_exists(ROOT_PATH . '/assets/js/app.js') ? filemtime(ROOT_PATH . '/assets/js/app.js') : time(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= e(asset_url('js/app.js?v=' . $adminScriptVersion)) ?>"></script>
</body>
</html>
