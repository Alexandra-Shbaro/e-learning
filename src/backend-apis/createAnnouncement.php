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

$secretKey = "zLqxD6TqRd5NV57jd8dVQj2jFK7fphgOWO/4mnCisjYX4RhWQDzxOqR4CXN0rh72IbpWoTSes3Cd6qABhT5ZSw=="; 

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

    if ($decoded->data->user_type !== 'instructor') {
        sendJsonResponse([
            "error" => "Only instructors are authorized to create announcements.",
        ], 403);  
    }

    $instructorId = $decoded->data->user_id;  

    $instructorCheckSql = "SELECT instructor_id FROM instructors WHERE instructor_id = ?";
    $stmt = $connection->prepare($instructorCheckSql);
    $stmt->bind_param("i", $instructorId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        sendJsonResponse([
            "error" => "Instructor ID does not exist.",
        ], 400);
    }

    $courseId = $_REQUEST['course_id'] ?? null;
    $announcementTitle = $_REQUEST['announcement_title'] ?? null;
    $announcementContent = $_REQUEST['announcement_content'] ?? null;

    if (!$courseId || !$announcementTitle || !$announcementContent) {
        sendJsonResponse([
            "error" => "Missing required fields (course_id, announcement_title, announcement_content)."
        ], 400);
    }

    $courseCheckSql = "SELECT course_id FROM courses WHERE course_id = ?";
    $stmt = $connection->prepare($courseCheckSql);
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        sendJsonResponse([
            "error" => "Course ID does not exist.",
        ], 400);
    }

    $announcedAt = date("Y-m-d H:i:s");

    $sql = "INSERT INTO announcements (instructor_id, course_id, announced_at, announcement_title, announcement_content) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $connection->prepare($sql);

    if ($stmt === false) {
        sendJsonResponse([
            "error" => "SQL Prepare Error for inserting announcement",
            "debug" => "SQL Prepare Failed: " . $connection->error
        ], 500);
    }

    $stmt->bind_param("iisss", $instructorId, $courseId, $announcedAt, $announcementTitle, $announcementContent);

    if ($stmt->execute()) {
        sendJsonResponse([
            "message" => "Announcement created successfully"
        ], 201);  
    } else {
        sendJsonResponse([
            "error" => "Failed to create announcement",
            "debug" => $stmt->error
        ], 500);
    }

} catch (Exception $e) {
    sendJsonResponse([
        "error" => "Token validation failed",
        "debug" => $e->getMessage()
    ], 401);
}

$connection->close();
?>
