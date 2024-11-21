<?php
include_once 'connection.php';
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

$secretKey = "zLqxD6TqRd5NV57jd8dVQj2jFK7fphgOWO/4mnCisjYX4RhWQDzxOqR4CXN0rh72IbpWoTSes3Cd6qABhT5ZSw=="; 

$response = array();
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $response['status'] = 'error';
    $response['message'] = 'Authorization token is required.';
    echo json_encode($response);
    exit;
}

$token = $matches[1];

try {
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
    $adminId = $decoded->data->admin_id;

    $actionType = $_POST['action_type'] ?? null;
    $actionDescription = $_POST['action_description'] ?? null;

    if (!$actionType || !$actionDescription) {
        $response['status'] = 'error';
        $response['message'] = 'Action type and description are required.';
        echo json_encode($response);
        exit;
    }

    $sql = "INSERT INTO admin_actions (action_type, action_description, admin_id) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ssi", $actionType, $actionDescription, $adminId);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Action logged successfully.';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to log action.';
    }
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Token validation failed.';
    $response['debug'] = $e->getMessage();
}

echo json_encode($response);
?>
