<?php

include 'connection.php';

header('Content-Type: application/json');

$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : (isset($_GET['user_id']) ? $_GET['user_id'] : null);

if ($user_id === null) {
    echo json_encode(['error' => 'user_id is required']);
    exit;
}

try {
    $stmt = $connection->prepare("SELECT user_type FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $stmt = $connection->prepare("UPDATE users SET isBanned = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    if ($user['user_type'] === 'instructor') {
        $stmt = $connection->prepare("UPDATE instructors SET isBanned = '1' WHERE instructor_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }

    echo json_encode(['success' => 'Instructor banned successfully']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
