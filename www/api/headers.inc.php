<?php

header('Connection: keep-alive');
header('Access-Control-Allow-Origin: '.$ALLOWORIGIN);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header("Access-Control-Allow-Headers: Content-Type, Authorization, Pragma, Cache-Control");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Max-Age: 1728000");
    http_response_code(200);
    exit();
}

header('Cache-Control: no-cache');

?>
