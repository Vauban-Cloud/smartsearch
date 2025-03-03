<?php

// API configuration
define('CONFIG_PATH', '../../.config.php');

if(!isset($_GET["db"])) ini_set('opcache.enable', 0);
include(CONFIG_PATH);
include("headers.inc.php");
include("utils.inc.php");

$apiUrl = 'https://api.ai.vauban.cloud/v1/rag/documents';

function getFromCache($cacheKey, $ttl = 60) {
    $cacheFile = sys_get_temp_dir() . '/cache_' . md5($cacheKey);
    if (file_exists($cacheFile)) {
        $fileAge = time() - filemtime($cacheFile);
        if ($fileAge<$ttl) { return unserialize(file_get_contents($cacheFile));
        }
    }
    return false;
}

function saveToCache($cacheKey, $data) {
    $cacheFile = sys_get_temp_dir() . '/cache_' . md5($cacheKey);
    return file_put_contents($cacheFile, serialize($data));
}

if(!isset($_GET["db"])) {
	echo json_encode(array_keys($APIKEY));
	exit();
} else $db=$_GET["db"];

// Set up the request headers
$headers = [ 'Authorization: Bearer ' . $APIKEY[$db], 'Accept: application/json' ];

// Execute the request
$response = getFromCache($db);
if ($response === false) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    saveToCache($db, $response);
    // Check for errors
    if (curl_errno($ch)) err(500,curl_error($ch));
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}

// Output the response as-is
echo $response;

?>
