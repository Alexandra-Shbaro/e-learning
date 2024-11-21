<?php
require "connection.php";
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

$secretKey = "zLqxD6TqRd5NV57jd8dVQj2jFK7fphgOWO/4mnCisjYX4RhWQDzxOqR4CXN0rh72IbpWoTSes3Cd6qABhT5ZSw==";

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;

if (!$authHeader) {
    sendJsonResponse(["error" => "Authorization token is required."], 401);
}

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    sendJsonResponse(["error" => "Invalid token format. Use 'Bearer <token>'"], 401);
}

$token = $matches[1];

try {
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

    $adminId = $decoded->data->admin_id;

    $adminCheckQuery = "SELECT 1 FROM admin_actions WHERE admin_id = ? LIMIT 1";
    $stmt = $connection->prepare($adminCheckQuery);

    if ($stmt === false) {
        sendJsonResponse(["error" => "Failed to prepare SQL query.", "debug" => $connection->error], 500);
    }

    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendJsonResponse(["error" => "Access denied. Invalid admin credentials."], 403);
    }

    $query = "SELECT student_id, student_name, student_email, enrolled_at FROM students ORDER BY enrolled_at DESC";

    $result = $connection->query($query);

    if (!$result) {
        sendJsonResponse(["error" => "Failed to fetch students.", "debug" => $connection->error], 500);
    }

    $students = $result->fetch_all(MYSQLI_ASSOC);

    sendJsonResponse(["students" => $students]);

} catch (Exception $e) {
    sendJsonResponse(["error" => "Token validation failed", "debug" => $e->getMessage()], 401);
}

$connection->close();
?>
