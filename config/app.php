<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('env_value')) {
    function env_value($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return $value;
    }
}

if (!function_exists('detect_base_url')) {
    function detect_base_url()
    {
        $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        $scriptName = str_replace('\\', '/', $scriptName);
        $directory = str_replace('\\', '/', dirname($scriptName));
        $directory = rtrim($directory, '/.');

        if (preg_match('#/admin$#', $directory) === 1) {
            $directory = str_replace('\\', '/', dirname($directory));
            $directory = rtrim($directory, '/.');
        }

        if ($directory === '' || $directory === '/') {
            return '';
        }

        return $directory;
    }
}

defined('ROOT_PATH') || define('ROOT_PATH', dirname(__DIR__));
defined('APP_NAME') || define('APP_NAME', env_value('APP_NAME', 'HPROMODE'));
defined('APP_TAGLINE') || define('APP_TAGLINE', env_value('APP_TAGLINE', 'Elegance Redefinie'));
defined('APP_SUPPORT_EMAIL') || define('APP_SUPPORT_EMAIL', env_value('APP_SUPPORT_EMAIL', 'concierge@hpromode.test'));
defined('APP_SUPPORT_PHONE') || define('APP_SUPPORT_PHONE', env_value('APP_SUPPORT_PHONE', '+234 800 555 0101'));
defined('LOW_STOCK_THRESHOLD') || define('LOW_STOCK_THRESHOLD', (int) env_value('LOW_STOCK_THRESHOLD', 5));
defined('BASE_URL') || define('BASE_URL', rtrim((string) env_value('APP_BASE_URL', detect_base_url()), '/'));
