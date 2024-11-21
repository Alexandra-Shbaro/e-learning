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

    if ($decoded->data->user_type !== 'instructor') {
        sendJsonResponse(["error" => "Access denied. Only instructors can create assignments."], 403);
    }

    $instructorId = $decoded->data->user_id;

    $assignmentTitle = $_POST['assignment_title'] ?? null;
    $assignmentDescription = $_POST['assignment_description'] ?? null;
    $dueTime = $_POST['due_time'] ?? null;
    $courseId = $_POST['course_id'] ?? null;

    if (!$assignmentTitle || !$assignmentDescription || !$dueTime || !$courseId) {
        sendJsonResponse(["error" => "All fields (assignment_title, assignment_description, due_time, course_id) are required."], 400);
    }

    $sql = "INSERT INTO assignments (assignment_title, assignment_description, due_time, course_id, instructor_id) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);

    if ($stmt === false) {
        sendJsonResponse(["error" => "Failed to prepare the SQL statement.", "debug" => $connection->error], 500);
    }

    $stmt->bind_param("sssii", $assignmentTitle, $assignmentDescription, $dueTime, $courseId, $instructorId);

    if ($stmt->execute()) {
        sendJsonResponse(["message" => "Assignment created successfully.", "assignment_id" => $stmt->insert_id]);
    } else {
        sendJsonResponse(["error" => "Failed to create the assignment.", "debug" => $stmt->error], 500);
    }

    $stmt->close();
} catch (Exception $e) {
    sendJsonResponse(["error" => "Token validation failed", "debug" => $e->getMessage()], 401);
}

$connection->close();
?>