<?php
require_once __DIR__ . '/app.php';
require_once ROOT_PATH . '/includes/functions.php';

$host = env_value('DB_HOST', 'localhost');
$port = (int) env_value('DB_PORT', 3306);
$dbname = env_value('DB_NAME', 'hpromode');
$username = env_value('DB_USER', 'root');
$password = env_value('DB_PASS', '');

if (strpos($host, ':') !== false) {
    $hostParts = explode(':', $host, 2);
    $host = trim($hostParts[0]);
    if (isset($hostParts[1]) && $hostParts[1] !== '') {
        $port = (int) $hostParts[1];
    }
}

function schema_check_query_exists($pdo, $sql)
{
    $statement = $pdo->query($sql);
    return $statement && $statement->fetch(PDO::FETCH_ASSOC);
}

function hpromode_schema_is_ready($pdo)
{
    $checks = array(
        "SHOW TABLES LIKE 'users'",
        "SHOW TABLES LIKE 'products'",
        "SHOW TABLES LIKE 'product_images'",
        "SHOW TABLES LIKE 'cart'",
        "SHOW TABLES LIKE 'promotions'",
        "SHOW COLUMNS FROM users LIKE 'password'",
        "SHOW COLUMNS FROM users LIKE 'phone'",
        "SHOW COLUMNS FROM products LIKE 'main_image'",
        "SHOW COLUMNS FROM products LIKE 'slug'",
        "SHOW COLUMNS FROM orders LIKE 'subtotal'",
        "SHOW COLUMNS FROM payments LIKE 'payment_note'",
        "SHOW COLUMNS FROM deliveries LIKE 'delivery_zone'"
    );

    foreach ($checks as $check) {
        if (!schema_check_query_exists($pdo, $check)) {
            return false;
        }
    }

    return true;
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        )
    );
} catch (PDOException $exception) {
    http_response_code(500);
    die(
        'Connexion a la base de donnees impossible : ' .
        htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') .
        '<br><br>Ouvrez <a href="' . htmlspecialchars(base_url('install.php'), ENT_QUOTES, 'UTF-8') . '">install.php</a> pour initialiser ou reparer la base locale.'
    );
}

if (!defined('HPROMODE_SKIP_SCHEMA_CHECK') && !hpromode_schema_is_ready($pdo)) {
    http_response_code(500);
    die(
        'La structure de la base de donnees HPROMODE ne correspond pas encore au projet.' .
        '<br><br>Ouvrez <a href="' . htmlspecialchars(base_url('install.php'), ENT_QUOTES, 'UTF-8') . '">install.php</a> pour reinitialiser la base locale avec le schema fourni.'
    );
}
