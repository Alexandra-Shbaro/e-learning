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

    $adminId = $decoded->data->admin_id ?? null;
    $email = $decoded->data->email ?? null;

    if (!$adminId || !$email) {
        $response['status'] = 'error';
        $response['message'] = 'Unauthorized access.';
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT instructor_id, isBanned FROM instructors";
    $result = $connection->query($sql);

    if ($result->num_rows > 0) {
        $instructors = array();

        while ($row = $result->fetch_assoc()) {
            $instructors[] = $row;
        }

        $response['status'] = 'success';
        $response['instructors'] = $instructors;
    } else {
        $response['status'] = 'success';
        $response['instructors'] = [];
        $response['message'] = 'No instructors found.';
    }
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Token validation failed.';
    $response['debug'] = $e->getMessage();
}

echo json_encode($response);
?>
