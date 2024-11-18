<?php
include "connection.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json'); 

try {
    $input = file_get_contents("php://input");

    $data = json_decode($input, true);

    if (!isset($data['username']) || empty($data['username']) ||
        !isset($data['email']) || empty($data['email']) ||
        !isset($data['password']) || empty($data['password'])) {
        echo json_encode(["success" => false, "message" => "Please fill in all required fields."]);
        exit();
    }

    $username = $data['username'];
    $email = $data['email'];
    $password = $data['password'];

    if (strlen($password) < 12) {
        echo json_encode(["success" => false, "message" => "Password must be at least 12 characters long."]);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Invalid email format."]);
        exit();
    }

    $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($email_count);
    $stmt->fetch();
    
    if ($email_count > 0) {
        echo json_encode(["success" => false, "message" => "A user with this email already exists."]);
        $stmt->close();
        $connection->close();
        exit();
    }

    $stmt->free_result();  

    $hashed_password = password_hash($password, PASSWORD_DEFAULT); 

    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($sql);

    if ($stmt === false) {
        echo json_encode(["success" => false, "message" => "Failed to prepare statement"]);
        exit();
    }

    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        echo json_encode([
            "success" => true,
            "message" => "User created successfully",
            "user_id" => $user_id
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Error creating user: " . $stmt->error]);
    }

    $stmt->close();
    $connection->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

?>