<?php
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

define('SITE_ROOT', __DIR__ . '/../');
define('VENDOR_DIR', SITE_ROOT . 'vendor/');
define('ENGINE_DIR', SITE_ROOT . 'engine/');
define('WWW_DIR', SITE_ROOT . 'public/');
define('TPL_DIR', SITE_ROOT . 'templates/');

define('NO_IMAGE', WWW_DIR . 'img/1.jpg');

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'geek_brains');
define('DB_PASS', '123123');
define('DB_NAME', 'geek_brains_shop');

require_once VENDOR_DIR . 'autoload.php';

