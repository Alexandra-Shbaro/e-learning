<?php

include "connection.php";
header('Content-Type: application/json');


$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : (isset($_GET['user_id']) ? $_GET['user_id'] : null);

if ($user_id === null) {
    echo json_encode(['error' => 'user_id is required']);
    exit;
}

try {
    // Check if user exists and get their user_type
    $stmt = $connection->prepare("SELECT user_type FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);  // 'i' means integer for user_id
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    // Set the user as banned in the users table
    $stmt = $connection->prepare("UPDATE users SET isBanned = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // If user is a student, ban them in the students table
    if ($user['user_type'] === 'student') {
        $stmt = $connection->prepare("UPDATE students SET isBanned = 1 WHERE student_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }

    // Respond with success
    echo json_encode(['success' => 'Student banned successfully']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

?>