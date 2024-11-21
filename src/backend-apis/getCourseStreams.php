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

    if (!in_array($decoded->data->user_type, ['instructor', 'student'])) {
        sendJsonResponse(["error" => "Access denied. Only instructors or students can access course details."], 403);
    }

    $courseId = $_GET['course_id'] ?? null;

    if (!$courseId) {
        sendJsonResponse(["error" => "Course ID is required as a query parameter."], 400);
    }

    $announcementsQuery = "SELECT announcement_id, instructor_id, course_id, announced_at, announcement_title, announcement_content 
                           FROM announcements 
                           WHERE course_id = ? 
                           ORDER BY announced_at DESC";

    $stmtAnnouncements = $connection->prepare($announcementsQuery);

    if ($stmtAnnouncements === false) {
        sendJsonResponse(["error" => "Failed to prepare the announcements query.", "debug" => $connection->error], 500);
    }

    $stmtAnnouncements->bind_param("i", $courseId);
    $stmtAnnouncements->execute();
    $resultAnnouncements = $stmtAnnouncements->get_result();
    $announcements = $resultAnnouncements->fetch_all(MYSQLI_ASSOC);

    $stmtAnnouncements->close();

    $assignmentsQuery = "SELECT assignment_id, assignment_title, assignment_description, due_time, course_id, instructor_id 
                         FROM assignments 
                         WHERE course_id = ? 
                         ORDER BY due_time DESC";

    $stmtAssignments = $connection->prepare($assignmentsQuery);

    if ($stmtAssignments === false) {
        sendJsonResponse(["error" => "Failed to prepare the assignments query.", "debug" => $connection->error], 500);
    }

    $stmtAssignments->bind_param("i", $courseId);
    $stmtAssignments->execute();
    $resultAssignments = $stmtAssignments->get_result();
    $assignments = $resultAssignments->fetch_all(MYSQLI_ASSOC);

    $stmtAssignments->close();

    sendJsonResponse([
        "course_id" => $courseId,
        "announcements" => $announcements,
        "assignments" => $assignments
    ]);

} catch (Exception $e) {
    sendJsonResponse(["error" => "Token validation failed", "debug" => $e->getMessage()], 401);
}

$connection->close();
?>
