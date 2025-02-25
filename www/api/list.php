<?php
// API configuration
include("../../.config.php");
$apiUrl = 'https://api.ai.vauban.cloud/v1/rag/documents';

header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

function getFromCache($cacheKey, $ttl = 86400) {
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

header('Cache-Control: no-cache');
header('Content-Type: application/json');

if(!isset($_GET["db"])) exit();
else $db=$_GET["db"];

// Set up the request headers
$headers = [ 'Authorization: Bearer ' . $apiKey[$db], 'Accept: application/json' ];

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
    if (curl_errno($ch)) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Curl error: ' . curl_error($ch)]);
        exit;
     }
     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
     curl_close($ch);
     http_response_code($httpCode);
}

// Output the response as-is
echo $response;

?>
