<?php
require "connection.php";
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

$secretKey = "zLqxD6TqRd5NV57jd8dVQj2jFK7fphgOWO/4mnCisjYX4RhWQDzxOqR4CXN0rh72IbpWoTSes3Cd6qABhT5ZSw=="; // REPLACE WITH YOUR ACTUAL SECRET KEY

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;

if (!$authHeader) {
    sendJsonResponse([
        "error" => "Authorization token is required.",
    ], 401);
}

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    sendJsonResponse([
        "error" => "Invalid token format. Use 'Bearer <token>'",
    ], 401);
}

$token = $matches[1];

try {
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (!isset($data['course_id'])) {
        sendJsonResponse([
            "error" => "Course ID is required"
        ], 400);
    }

    $courseId = (int)$data['course_id'];
    $userId = $decoded->data->user_id;

    $userCheckSql = "SELECT student_id FROM students WHERE student_id = ?";
    $userCheckStmt = $connection->prepare($userCheckSql);
    
    if ($userCheckStmt === false) {
        sendJsonResponse([
            "error" => "SQL Prepare Error for user check",
            "debug" => "User Check SQL Prepare Failed: " . $connection->error
        ], 500);
    }

    $userCheckStmt->bind_param("i", $userId);
    $userCheckStmt->execute();
    $userResult = $userCheckStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        sendJsonResponse([
            "error" => "User not found in students table",
            "debug" => "User ID: " . $userId
        ], 404);
    }

    $checkSql = "SELECT enrollment_id FROM course_enrollments WHERE student_id = ? AND course_id = ?";
    $checkStmt = $connection->prepare($checkSql);
    
    if ($checkStmt === false) {
        sendJsonResponse([
            "error" => "SQL Prepare Error for enrollment check",
            "debug" => "Check SQL Prepare Failed: " . $connection->error
        ], 500);
    }

    $checkStmt->bind_param("ii", $userId, $courseId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        sendJsonResponse([
            "error" => "Already enrolled in this course"
        ], 409);
    }
    
    $insertSql = "INSERT INTO course_enrollments (student_id, course_id, enrolled_at) VALUES (?, ?, NOW())";
    $insertStmt = $connection->prepare($insertSql);
    
    if ($insertStmt === false) {
        sendJsonResponse([
            "error" => "SQL Prepare Error for insert",
            "debug" => "Insert SQL Prepare Failed: " . $connection->error
        ], 500);
    }

    $insertStmt->bind_param("ii", $userId, $courseId);
    
    if (!$insertStmt->execute()) {
        sendJsonResponse([
            "error" => "Enrollment failed",
            "debug" => $insertStmt->error
        ], 500);
    }

    sendJsonResponse([
        "success" => true,
        "message" => "Successfully enrolled",
        "enrollment_id" => $insertStmt->insert_id
    ], 201);

} catch (Exception $e) {
    sendJsonResponse([
        "error" => "Token validation failed",
        "debug" => $e->getMessage()
    ], 401);
}

$connection->close();
?>