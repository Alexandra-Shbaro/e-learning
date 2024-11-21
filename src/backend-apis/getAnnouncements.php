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
    
    $userId = $decoded->data->user_id;

    // SQL query to fetch announcements related to courses the student is enrolled in
    $sql = "
        SELECT a.announcement_id, a.instructor_id, a.course_id, a.announced_at, a.announcement_title, a.announcement_content
        FROM announcements a
        JOIN course_enrollments ce ON a.course_id = ce.course_id
        WHERE ce.student_id = ? 
        ORDER BY a.announced_at DESC
    ";
    
    $stmt = $connection->prepare($sql);
    
    if ($stmt === false) {
        sendJsonResponse([
            "error" => "SQL Prepare Error for fetching announcements",
            "debug" => "Announcements SQL Prepare Failed: " . $connection->error
        ], 500);
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $announcements = [];
        
        while ($row = $result->fetch_assoc()) {
            $announcements[] = [
                'announcement_id' => $row['announcement_id'],
                'instructor_id' => $row['instructor_id'],
                'course_id' => $row['course_id'],
                'announced_at' => $row['announced_at'],
                'announcement_title' => $row['announcement_title'],
                'announcement_content' => $row['announcement_content']
            ];
        }
        
        sendJsonResponse($announcements);
    } else {
        sendJsonResponse([
            "message" => "No announcements found for your enrolled courses."
        ], 404);
    }

} catch (Exception $e) {
    sendJsonResponse([
        "error" => "Token validation failed",
        "debug" => $e->getMessage()
    ], 401);
}

$connection->close();
?>
