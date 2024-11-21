<?php
require "connection.php";
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);  // Return response with status 200 (OK) to let the browser know it's allowed
}

error_reporting(E_ALL);
ini_set('display_errors', 1);


// Helper function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

$secretKey = "zLqxD6TqRd5NV57jd8dVQj2jFK7fphgOWO/4mnCisjYX4RhWQDzxOqR4CXN0rh72IbpWoTSes3Cd6qABhT5ZSw=="; 

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? null;

// Check if Authorization header is provided
if (!$authHeader) {
    sendJsonResponse([
        "error" => "Authorization token is required.",
    ], 401);
}

// Validate the token format (Bearer <token>)
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    sendJsonResponse([
        "error" => "Invalid token format. Use 'Bearer <token>'",
    ], 401);
}

$token = $matches[1];

try {
    // Decode the token
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
    $userId = $decoded->data->user_id; // Extract the student_id from the token

    // Fetch courses the student is enrolled in
    $sql = "
        SELECT c.course_id, c.course_name, c.course_description
        FROM courses c
        JOIN course_enrollments ce ON c.course_id = ce.course_id
        WHERE ce.student_id = ?
    ";

    $stmt = $connection->prepare($sql);
    if ($stmt === false) {
        sendJsonResponse([
            "error" => "SQL Prepare Error for fetching courses",
            "debug" => "SQL Prepare Failed: " . $connection->error
        ], 500);
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the student is enrolled in any courses
    if ($result->num_rows > 0) {
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = [
                'course_id' => $row['course_id'],
                'course_name' => $row['course_name'],
                'course_description' => $row['course_description']
            ];
        }
        sendJsonResponse($courses);
    } else {
        sendJsonResponse([
            "message" => "No courses found for this student"
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
