<?php

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function array_value($array, $key, $default = '')
{
    if (is_array($array) && isset($array[$key])) {
        return $array[$key];
    }

    return $default;
}

function base_url($path = '')
{
    $path = ltrim((string) $path, '/');

    if ($path === '') {
        return BASE_URL !== '' ? BASE_URL : '/';
    }

    return (BASE_URL !== '' ? BASE_URL : '') . '/' . $path;
}

function asset_url($path)
{
    return base_url('assets/' . ltrim((string) $path, '/'));
}

function redirect($path)
{
    if (preg_match('#^https?://#i', $path) === 1) {
        header('Location: ' . $path);
        exit;
    }

    header('Location: ' . base_url($path));
    exit;
}

function flash($key, $message = null)
{
    if (func_num_args() === 2) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (isset($_SESSION['flash'][$key])) {
        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    return null;
}

function current_user()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function current_admin()
{
    return isset($_SESSION['admin']) ? $_SESSION['admin'] : null;
}

function is_logged_in()
{
    return current_user() !== null;
}

function is_admin()
{
    return current_admin() !== null;
}

function require_login()
{
    if (!is_logged_in()) {
        flash('danger', 'Veuillez vous connecter pour finaliser cette action.');
        redirect('login.php');
    }
}

function require_admin()
{
    if (!is_admin()) {
        flash('danger', 'Connexion administrateur requise.');
        redirect('admin/login.php');
    }
}

function format_price($price)
{
    return number_format((float) $price, 2, ',', ' ') . ' $';
}

function generate_slug($value)
{
    $value = trim(mb_strtolower($value, 'UTF-8'));
    $map = array(
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a',
        'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ñ' => 'n',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y',
    );
    $value = strtr($value, $map);
    $value = preg_replace('/[^a-z0-9]+/u', '-', $value);
    $value = trim($value, '-');

    return $value !== '' ? $value : 'article';
}

function badge_class($status)
{
    $normalized = mb_strtolower(trim((string) $status), 'UTF-8');

    switch ($normalized) {
        case 'validé':
        case 'valide':
        case 'paid':
        case 'livrée':
        case 'livree':
        case 'livré':
        case 'active':
            return 'success';
        case 'en attente':
        case 'pending':
        case 'en préparation':
        case 'preparation':
        case 'draft':
            return 'warning';
        case 'échoué':
        case 'echoue':
        case 'failed':
        case 'rupture':
        case 'expédiée':
        case 'expediee':
        case 'inactive':
            return 'danger';
        default:
            return 'secondary';
    }
}

function parse_options($value)
{
    if ($value === null || trim($value) === '') {
        return array();
    }

    $parts = array_map('trim', explode(',', $value));
    $filtered = array();

    foreach ($parts as $part) {
        if ($part !== '' && !in_array($part, $filtered, true)) {
            $filtered[] = $part;
        }
    }

    return $filtered;
}

function make_cart_key($productId, $size = '', $color = '')
{
    return (int) $productId . '|' . trim((string) $size) . '|' . trim((string) $color);
}

function cart_items_count()
{
    $count = 0;
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();

    foreach ($cart as $item) {
        $count += (int) array_value($item, 'quantity', 0);
    }

    return $count;
}

function persist_cart($pdo)
{
    if (!is_logged_in()) {
        return;
    }

    $user = current_user();
    $userId = (int) $user['id'];
    $pdo->prepare('DELETE FROM cart WHERE user_id = ?')->execute(array($userId));

    if (empty($_SESSION['cart'])) {
        return;
    }

    $statement = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity, size, color) VALUES (?, ?, ?, ?, ?)');
    foreach ($_SESSION['cart'] as $item) {
        $statement->execute(array(
            $userId,
            (int) $item['product_id'],
            (int) $item['quantity'],
            array_value($item, 'size', ''),
            array_value($item, 'color', ''),
        ));
    }
}

function merge_cart_for_user($pdo, $userId)
{
    $storedItems = array();
    $statement = $pdo->prepare('SELECT product_id, quantity, size, color FROM cart WHERE user_id = ?');
    $statement->execute(array((int) $userId));

    foreach ($statement->fetchAll() as $row) {
        $size = array_value($row, 'size', '');
        $color = array_value($row, 'color', '');
        $key = make_cart_key((int) $row['product_id'], $size, $color);
        $storedItems[$key] = array(
            'product_id' => (int) $row['product_id'],
            'quantity' => (int) $row['quantity'],
            'size' => $size,
            'color' => $color,
        );
    }

    $sessionCart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
    foreach ($sessionCart as $key => $item) {
        if (isset($storedItems[$key])) {
            $storedItems[$key]['quantity'] += (int) $item['quantity'];
        } else {
            $storedItems[$key] = array(
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) $item['quantity'],
                'size' => array_value($item, 'size', ''),
                'color' => array_value($item, 'color', ''),
            );
        }
    }

    $_SESSION['cart'] = $storedItems;
    persist_cart($pdo);
}

function add_to_cart($pdo, $productId, $quantity, $size, $color)
{
    $key = make_cart_key($productId, $size, $color);

    if (!isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key] = array(
            'product_id' => (int) $productId,
            'quantity' => 0,
            'size' => $size,
            'color' => $color,
        );
    }

    $_SESSION['cart'][$key]['quantity'] += (int) $quantity;
    persist_cart($pdo);
}

function remove_from_cart($pdo, $cartKey)
{
    unset($_SESSION['cart'][$cartKey]);
    persist_cart($pdo);
}

function clear_cart($pdo)
{
    $_SESSION['cart'] = array();
    unset($_SESSION['promo_code']);
    persist_cart($pdo);
}

function fetch_cart_items($pdo)
{
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
    if (empty($cart)) {
        return array();
    }

    $productIds = array();
    foreach ($cart as $item) {
        $productId = (int) $item['product_id'];
        if (!in_array($productId, $productIds, true)) {
            $productIds[] = $productId;
        }
    }

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $statement = $pdo->prepare("SELECT * FROM products WHERE id IN ({$placeholders})");
    $statement->execute($productIds);

    $products = array();
    foreach ($statement->fetchAll() as $product) {
        $products[(int) $product['id']] = $product;
    }

    $items = array();
    foreach ($cart as $key => $item) {
        $productId = (int) $item['product_id'];
        if (!isset($products[$productId])) {
            continue;
        }

        $product = $products[$productId];
        $unitPrice = ($product['promo_price'] !== null && $product['promo_price'] !== '')
            ? (float) $product['promo_price']
            : (float) $product['price'];
        $quantity = (int) $item['quantity'];

        $items[] = array(
            'key' => $key,
            'product' => $product,
            'size' => array_value($item, 'size', ''),
            'color' => array_value($item, 'color', ''),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $unitPrice * $quantity,
        );
    }

    return $items;
}

function get_active_promotion($pdo, $code = null)
{
    $sql = 'SELECT * FROM promotions WHERE is_active = 1 AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE())';
    $params = array();

    if ($code !== null && trim($code) !== '') {
        $sql .= ' AND code = ?';
        $params[] = trim($code);
    }

    $sql .= ' ORDER BY id DESC LIMIT 1';
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $promotion = $statement->fetch();

    return $promotion ? $promotion : null;
}

function calculate_discount($promotion, $subtotal)
{
    if ($promotion === null || $subtotal <= 0) {
        return 0;
    }

    $value = (float) $promotion['discount_value'];
    if ($promotion['discount_type'] === 'fixed') {
        return min($subtotal, $value);
    }

    return min($subtotal, ($subtotal * $value) / 100);
}

function set_promo_code($code)
{
    $code = trim((string) $code);

    if ($code === '') {
        unset($_SESSION['promo_code']);
        return;
    }

    $_SESSION['promo_code'] = $code;
}

function get_promo_code()
{
    return isset($_SESSION['promo_code']) ? $_SESSION['promo_code'] : null;
}

function calculate_cart_totals($pdo)
{
    $items = fetch_cart_items($pdo);
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['line_total'];
    }

    $promotion = get_active_promotion($pdo, get_promo_code());
    $discount = calculate_discount($promotion, $subtotal);

    return array(
        'items' => $items,
        'subtotal' => $subtotal,
        'promotion' => $promotion,
        'discount' => $discount,
        'total' => max(0, $subtotal - $discount),
    );
}

function determine_delivery_fee($method, $zone)
{
    $method = mb_strtolower(trim((string) $method), 'UTF-8');
    $zone = mb_strtolower(trim((string) $zone), 'UTF-8');

    if ($method === 'retrait boutique') {
        return 0;
    }

    $fees = array(
        'livraison standard' => array(
            'centre-ville' => 12,
            'national' => 24,
            'international' => 55,
        ),
        'livraison express' => array(
            'centre-ville' => 22,
            'national' => 36,
            'international' => 75,
        ),
    );

    if (isset($fees[$method]) && isset($fees[$method][$zone])) {
        return $fees[$method][$zone];
    }

    return 18;
}

function generate_order_number()
{
    return 'HPR-' . date('Ymd') . '-' . mt_rand(10000, 99999);
}

function sync_user_session($pdo, $userId)
{
    $statement = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $statement->execute(array((int) $userId));
    $user = $statement->fetch();

    if ($user) {
        $_SESSION['user'] = $user;
    }
}

function product_gallery($pdo, $productId, $fallbackImage)
{
    $statement = $pdo->prepare('SELECT image_path, alt_text FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC');
    $statement->execute(array((int) $productId));
    $images = $statement->fetchAll();

    if (empty($images)) {
        return array(
            array('image_path' => $fallbackImage, 'alt_text' => 'Visuel produit'),
        );
    }

    return $images;
}

function stock_state($stock)
{
    $stock = (int) $stock;

    if ($stock <= 0) {
        return 'Rupture de stock';
    }

    if ($stock <= LOW_STOCK_THRESHOLD) {
        return 'Stock faible';
    }

    return 'Disponible';
}
