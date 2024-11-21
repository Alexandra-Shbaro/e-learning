<?php
include_once 'connection.php';
require 'vendor/autoload.php';

use Firebase\JWT\JWT;

header('Content-Type: application/json');

$secretKey = "zLqxD6TqRd5NV57jd8dVQj2jFK7fphgOWO/4mnCisjYX4RhWQDzxOqR4CXN0rh72IbpWoTSes3Cd6qABhT5ZSw=="; 

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$email || !$password) {
        $response['status'] = 'error';
        $response['message'] = 'Email and password are required.';
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT admin_id, password FROM admin_actions WHERE email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid email or password.';
        echo json_encode($response);
        exit;
    }

    $admin = $result->fetch_assoc();

    if (!password_verify($password, $admin['password'])) {
        $response['status'] = 'error';
        $response['message'] = 'Invalid email or password.';
        echo json_encode($response);
        exit;
    }

    $payload = [
        'iss' => 'your-website', 
        'aud' => 'your-website',
        'iat' => time(),
        'exp' => time() + (60 * 60), 
        'data' => [
            'admin_id' => $admin['admin_id'],
            'email' => $email
        ]
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    $response['status'] = 'success';
    $response['token'] = $jwt;
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
