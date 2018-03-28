<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once 'vendor/autoload.php';

use SITE\Helpers\Config;
use SITE\Helpers\Template;
use SITE\Models\AjaxRequest;

ob_start();

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    $origin = $_SERVER['HTTP_REFERER'];
} elseif (isset($_SERVER['HTTP_ORIGIN']) && !empty($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];
} else {
    $origin = '';
}

if (!empty($origin)) {
    $parsedURL = parse_url($origin);
    $port = isset($parsedURL['port']) ? ':' . $parsedURL['port'] : '';
    $origin = $parsedURL['scheme'] . '://' . $parsedURL['host'] . $port;
    header('Access-Control-Allow-Origin: ' . $origin);
}

Config::init();
if (isset($_GET['token'])) {
    $_POST['token'] = $_GET['token'];
}
$url = $_SERVER['REQUEST_URI'];
$pos = strpos($_SERVER['REQUEST_URI'], 'api');
//for page loading
if ($pos === 1) {
    $ajaxResponse = AjaxRequest::getInstance()->getResponse();
    ob_end_clean();
    $ajaxResponse->printResponse();
} else {
    Template::init();
}