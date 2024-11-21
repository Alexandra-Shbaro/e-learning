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
    // Decode JWT
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
    
    // Get the user_id (student_id) from the token
    $userId = $decoded->data->user_id;

    // Fetch the courses the student is enrolled in
    $enrolledCoursesSql = "
        SELECT c.course_id, c.course_name, c.course_description 
        FROM courses c
        WHERE NOT EXISTS (
            SELECT 1 FROM course_enrollments ce 
            WHERE ce.course_id = c.course_id AND ce.student_id = ?
        )
    ";
    
    // Prepare the SQL statement
    $stmt = $connection->prepare($enrolledCoursesSql);
    
    if ($stmt === false) {
        sendJsonResponse([
            "error" => "SQL Prepare Error for fetching non-enrolled courses",
            "debug" => "Enrollment SQL Prepare Failed: " . $connection->error
        ], 500);
    }
    
    // Bind the student_id from JWT
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the student is not enrolled in any courses
    if ($result->num_rows > 0) {
        $coursesNotEnrolled = [];
        
        // Fetch courses the student is not enrolled in
        while ($row = $result->fetch_assoc()) {
            $coursesNotEnrolled[] = [
                'course_id' => $row['course_id'],
                'course_name' => $row['course_name'],
                'course_description' => $row['course_description']
            ];
        }
        
        // Send response with courses
        sendJsonResponse($coursesNotEnrolled);
    } else {
        sendJsonResponse([
            "message" => "No available courses the student is not enrolled in."
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
