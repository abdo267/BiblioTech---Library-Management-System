<?php

if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// Site Settings
define('SITE_NAME', 'BiblioTech');
define('TIMEZONE', 'Africa/Cairo');
date_default_timezone_set(TIMEZONE);

// Session lifetime 
define('SESSION_LIFETIME', 1800);

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'library_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Directories
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VIEWS_PATH', BASE_PATH . '/views');
define('UPLOAD_PATH', BASE_PATH . '/public/uploads/covers');
define('EMAIL_LOG_PATH', BASE_PATH . '/email_simulation.log');

// Dynamic Base URL detection
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('/index.php', '', $scriptName);
$baseUrl = $protocol . $host . $scriptDir;
define('BASE_URL', rtrim($baseUrl, '/'));

// Google Books API
define('GOOGLE_BOOKS_API_URL', 'https://www.googleapis.com/books/v1/volumes');

// Load local config overrides
$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    require $localConfig;
}

// API Keys 
define('GOOGLE_BOOKS_API_KEY', $googleBooksApiKey ?? '');
define('STRIPE_PUBLISHABLE_KEY', $stripePublishableKey ?? '');
define('STRIPE_SECRET_KEY', $stripeSecretKey ?? '');
define('STRIPE_WEBHOOK_SECRET', $stripeWebhookSecret ?? '');

// Payment Settings
define('FINE_RATE_PER_DAY', 100.00);
define('PAYMENT_CURRENCY', 'egp');
define('CURRENCY_SYMBOL', 'EGP');
