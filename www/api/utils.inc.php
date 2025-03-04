<?php

// Return error
function err($code,$msg) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $msg
        ]);
        exit();
}

// Auth
function is_auth($wait=false) {
	session_start();
	if($wait) sleep(0.25);
        if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] != true) err(401,'Authentication required');
        else return true;
}

// Cleanup string
function sanitize($input) {
    return preg_replace('/[^a-zA-Z0-9_\-\. &]/','',$input);
}

// Add Key to config file
function addKey($basename, $value) {
    $basename = sanitize($basename);
    if (empty($basename)) return false;
    global $APIKEY;
    if(isset($APIKEY[$basename])) return false;
    $content = file_get_contents(CONFIG_PATH);
    if ($content === false) { return false; }
    if (preg_match_all('/\$APIKEY\[.*\].*\;/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
        $lastMatch = end($matches[0]);
        $position = $lastMatch[1] + strlen($lastMatch[0]);
        $newLine = "\n\$APIKEY[\"" . sanitize($basename) . "\"] = '" . sanitize($value) . "';";
        $newContent = substr($content, 0, $position) . $newLine . substr($content, $position);
        $result = file_put_contents(CONFIG_PATH, $newContent);
        return $result !== false;
    }
    return false;
}

// Rename Key in config file
function renameKey($basename, $newname) {
    $basename = sanitize($basename);
    $newname = sanitize($newname);
    if (empty($basename) || empty($newname)) return false;
    global $APIKEY;
    if(!isset($APIKEY[$basename])) return false;
    if(isset($APIKEY[$newname])) return false;
    $content = file_get_contents(CONFIG_PATH);
    if ($content === false) { return false; }
    $currentValue = $APIKEY[$basename];
    $pattern = '/(\$APIKEY\["' . preg_quote($basename, '/') . '"\]\s*=\s*)[\'"].*?[\'"];/';
    $replacement = '$APIKEY["' . $newname . '"] = \'' . $currentValue . '\';';
    $newContent = preg_replace($pattern, $replacement, $content, -1, $count);
    if ($count === 0) { return false; }
    $result = file_put_contents(CONFIG_PATH, $newContent);
    return $result !== false;
}

// Update Key in config file
function updateKey($basename, $newkey) {
    $basename = sanitize($basename);
    if (empty($basename)) return false;
    global $APIKEY;
    if(!isset($APIKEY[$basename])) return false;
    $content = file_get_contents(CONFIG_PATH);
    if ($content === false) { return false; }
    $pattern = '/(\$APIKEY\["' . preg_quote($basename, '/') . '"\]\s*=\s*)[\'"].*?[\'"];/';
    $replacement = '$1\'' . sanitize($newkey) . '\';';
    $newContent = preg_replace($pattern, $replacement, $content, -1, $count);
    if ($count === 0) { return false; }
    $result = file_put_contents(CONFIG_PATH, $newContent);
    return $result !== false;
}

// Delete Key from config file
function delKey($basename) {
    $basename = sanitize($basename);
    if (empty($basename)) return false;
    global $APIKEY;
    if(!isset($APIKEY[$basename])) return false;
    $content = file_get_contents(CONFIG_PATH);
    if ($content === false) return false;
    $pattern = '/\$APIKEY\["' . preg_quote($basename, '/') . '"\]\s*=\s*[\'"].*?[\'"];/';
    $newContent = preg_replace($pattern, '', $content, -1, $count);
    if ($count === 0) return false;
    $newContent = preg_replace('/(\r\n|\n|\r){3,}/', "\n\n", $newContent);
    $result = file_put_contents(CONFIG_PATH, $newContent);
    return $result !== false;
}

// Upload Files(s) to local folder
function upload_to_folder($docBase, $folder, $files) {
    if (empty($files['name'])) return false;
    $targetDir = "../files/$docBase/$folder/";
    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) return false;
    $fileCount = count($files['name']);
    for ($i = 0; $i < $fileCount; $i++) {
        $filename = $files['name'][$i];
        $tmp_path = $files['tmp_name'][$i];
        $error = $files['error'][$i];
        $size = $files['size'][$i];
        if ($error !== UPLOAD_ERR_OK || $size <= 0) return false;
        $safeFilename = preg_replace('/[^\w\-\.]/', '_', $filename);
        $targetFile = $targetDir . $safeFilename;
	if (copy($tmp_path, $targetFile)) {
            $result['files'][] = $safeFilename;
        }
    }
    return $result;
}

// Upload file to RAG using Vauban API
function uploadFilesToAI($docBase,$folder,$files) {
    global $APIKEY;
    if (empty($files) || empty($docBase) || empty($APIKEY[$docBase])) {
        return [
            'success' => false,
            'message' => 'Missing required parameters'
        ];
    }
    $url = "https://api.vauban.cloud/v1/rag/documents?folder=" . urlencode(sanitize($folder));
    $headers = [ "Authorization: Bearer " . $APIKEY[$docBase], "accept: application/json" ];
    // Initialize curl
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    // Create a CURLFile array
    $curlFiles = [];
    $fileCount = count($files['name']);
    for ($i = 0; $i < $fileCount; $i++) {
        $filename = $files['name'][$i];
        $type = $files['type'][$i];
        $tmp_path = $files['tmp_name'][$i];
        $error = $files['error'][$i];
        $size = $files['size'][$i];
        if ($error === UPLOAD_ERR_OK && $size>0) {
            $curlFiles[] = new CURLFile($tmp_path,$type,$filename);
        }
    }
    // Build a custom POST request with multiple 'files' fields
    $boundary = uniqid();
    $delimiter = '-------------' . $boundary;
    $postData = '';
    // Add each file with the same field name 'files'
    foreach ($curlFiles as $curlFile) {
        $postData .= "--" . $delimiter . "\r\n";
        $postData .= 'Content-Disposition: form-data; name="files"; filename="' . $curlFile->getPostFilename() . '"' . "\r\n";
        $postData .= 'Content-Type: ' . $curlFile->getMimeType() . "\r\n\r\n";
        $postData .= file_get_contents($curlFile->getFilename()) . "\r\n";
    }
    // Close the request
    $postData .= "--" . $delimiter . "--\r\n";
    // Set the content type with boundary and the post data
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, [
        'Content-Type: multipart/form-data; boundary=' . $delimiter,
        'Content-Length: ' . strlen($postData)
    ]));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    if ($response === false) {
        return [
            'success' => false,
            'message' => "cURL Error: $error",
            'status' => $status
	];
    }
    $responseData = json_decode($response, true);
    return [
        'success' => ($status >= 200 && $status < 300),
        'message' => isset($responseData['message']) ? $responseData['message'] : 'Request completed',
        'status' => $status
    ];
}

?>
