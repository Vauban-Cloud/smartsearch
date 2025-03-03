<?php

define('CONFIG_PATH', '../../.config.php');

include(CONFIG_PATH);
include("utils.inc.php");
include("headers.inc.php");

// Headers and Cookie settings
ini_set('session.cookie_secure', 'true');
ini_set('session.cookie_httponly', 'true');
ini_set('session.cookie_samesite', $SAMESITE); // Strict in prod
// ini settings for large file uploads
ini_set('upload_max_filesize', '42M');
ini_set('post_max_size', '43M');
ini_set('max_execution_time', '300');
ini_set('memory_limit', '256M');

// Get the request data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { $data = $_POST; }

// Get the requested action
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'login':
        if (isset($data['username']) && isset($data['password']) && $data['username'] === $USERNAME && $data['password'] === $HASHEDPASS) {
	    session_start();
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] =  $data['username'];
            echo json_encode([
                'success' => true,
                'message' => 'Authentication successful',
                'username' => $data['username']
            ]);
        } else err(401,'Invalid credentials');
        break;
        
    case 'logout':
	setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => '/', 'domain' => '', 'secure' => true, 'httponly' => true, 'samesite' => $SAMESITE]);
        session_start();
        session_destroy();
        echo json_encode([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
        break;
        
    case 'update_api_key':
	is_auth();
	$r=updateKey($data["docBase"],$data["apiKey"]);
        echo json_encode([
            'success' => $r,
            'message' => $r ? 'API key updated successfully' : 'Failed to update API key'
        ]);
        break;

    case 'rename_doc_base':
        is_auth();
        $r=renameKey($data["docBase"],$data["newName"]);
        echo json_encode([
            'success' => $r,
            'message' => $r ? 'API key renamed successfully' : 'Failed to rename API key'
        ]);
        break;

    case 'add_doc_base':
        is_auth();
        $r=addKey($data["name"],$data["apiKey"]);
	sleep(1);
	include(CONFIG_PATH);
        echo json_encode([
            'success' => $r,
            'message' => $r ? 'API key added successfully' : 'Failed to add API key'
        ]);
        break;

    case 'delete_doc_base':
        is_auth();
        $r=delKey($data["docBase"]);
        sleep(1);
        include(CONFIG_PATH);
        echo json_encode([
            'success' => $r,
            'message' => $r ? 'API key deleted successfully' : 'Failed to delete API key'
        ]);
        break;

    case 'upload':
	is_auth();
	echo json_encode(uploadFilesToAI($data['db'],$data['folder'],$_FILES["files"]));
        break;

    case 'check':
        is_auth(true);
	if($USERNAME=="" || $HASHEDPASS=="") $msg="Disabled";
	else $msg="Good to go";
        echo json_encode([
            'success' => true,
            'message' => $msg
        ]);
        break;

    default:
        err(400,'Invalid action');
}
?>
