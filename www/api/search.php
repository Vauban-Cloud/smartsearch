<?php

include('../../.config.php');

header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

header('Cache-Control: no-cache');

if($STREAMING) {
  header('Content-Type: text/event-stream');
} else {
  header('Content-Type: application/json');
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);
$messages = [];
foreach ($data['context'] as $message) {
    $messages[] = ["role" => "user", "content" => $message['q']];
    $messages[] = ["role" => "assistant", "content" => $message['a']];
}
$messages[] = ["role" => "user", "content" => $data['question']];

$lang = $data['lang'] ?? "en";
$apiRequest = [
    "model" => $data['smart'] ? "chat-v1" : "fast-v1",
    "messages" => $messages,
    "temperature" => 0.1,
    "top_p" => 0.1,
    "top_k" => 10,
    "lang" => $lang,
    "presence_penalty" => -0.5,
    "frequency_penalty" => 0.3,
    "seed" => 0,
    "score_subject_threshold" => 0.35,
    "score_threshold" => 0.35,
    "below_threshold_response" => "ERROR_FILTER",
    "n" => 1,
    "stream" => $STREAMING,
    "max_tokens" => 1024,
    "rag" => true,
    "repetition_penalty" => 1,
    "additional_prompt" => $add_prompt[$lang],
    "deferred" => false
];

function err($msg) {
        http_response_code(400);
        echo json_encode(['error' => $msg]);
        exit;
}

// Endpoint
if($apiKey[$data['base']]==null) err("Base and API key mismatch");
$openaiEndpoint = 'https://api.vauban.cloud/v1/chat/completions';
$headers = [ "Authorization: Bearer ".$apiKey[$data['base']], "Content-Type: application/json" ];
$ch = curl_init($openaiEndpoint);

if($STREAMING) {
    // TODO
  curl_setopt_array($ch, [
    CURLOPT_TIMEOUT        => 90,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($apiRequest),
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_FAILONERROR    => true,
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_WRITEFUNCTION  => function ($ch, $chunk) {
        echo $chunk;
        ob_flush();
        flush();
        return strlen($chunk);
    },
  ]);
  curl_exec($ch);
} else {
  curl_setopt_array($ch, [
    CURLOPT_TIMEOUT        => 90,
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_FAILONERROR    => true,
    CURLOPT_POSTFIELDS => json_encode($apiRequest)
  ]);
  $response = curl_exec($ch);
  $aiResponse = json_decode($response, true);
  if($aiResponse===null) err($aiResponse['error']);
  $response = [
    'sources' => !empty($aiResponse['sources']) ? array_map(function($source) {
        return ['filename' => $source['filename'], 'page' => $source['page_nbr']];
    }, $aiResponse['sources']) : [],
    'content' => isset($aiResponse['choices'][0]['message']['content']) ?
        $aiResponse['choices'][0]['message']['content'] : ''
  ];
  if (curl_errno($ch)) {
    echo "event: error\ndata: " . json_encode(['error' => curl_error($ch)]) . "\n\n";
    ob_flush();
    flush();
  } else {
    echo json_encode($response);
  }
}

curl_close($ch);

?>
