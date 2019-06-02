<?php
$start_time = microtime(true);
define('DEV', true);

if (DEV) {
    ini_set('display_errors', 1);
    ini_set('error_reporting', E_ALL);
}

require_once('headers.php');

date_default_timezone_set("Asia/Yekaterinburg");

define('ROOT_DIR', __DIR__ . DIRECTORY_SEPARATOR);
define('boot_manager', true);

require_once(ROOT_DIR . DIRECTORY_SEPARATOR . 'directions.php');
require_once(CORE_DIR . 'functions/main.php');

$env = get_env();
if (count($env) === 0) {
    die("create .env {database: db:'', user:'', pass:'', charset:''}");
}

require_once(CORE_DIR . 'core.php');
require_once(ROOT_DIR . DIRECTORY_SEPARATOR. 'router.php');

$time = microtime(true) - $start_time;

//if (DEV){
//    print_r(json_encode(['start_time' => $time]));
//}
