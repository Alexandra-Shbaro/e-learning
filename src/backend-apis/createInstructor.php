<?php
include "connection.php";
require 'vendor/autoload.php';

use Firebase\JWT\JWT;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

$secretKey = "zLqxD6TqRd5NV57jd8dVQj2jFK7fphgOWO/4mnCisjYX4RhWQDzxOqR4CXN0rh72IbpWoTSes3Cd6qABhT5ZSw==";

try {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (
        !isset($data['username']) || empty($data['username']) ||
        !isset($data['email']) || empty($data['email']) ||
        !isset($data['password']) || empty($data['password'])
    ) {
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

    $sql = "INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, 'instructor')";
    $stmt = $connection->prepare($sql);
    if ($stmt === false) {
        echo json_encode(["success" => false, "message" => "Failed to prepare statement"]);
        exit();
    }

    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        $sql = "INSERT INTO instructors (instructor_id) VALUES (?)";
        $instructorStmt = $connection->prepare($sql);
        if ($instructorStmt === false) {
            echo json_encode(["success" => false, "message" => "Failed to prepare instructor INSERT statement."]);
            exit();
        }
        $instructorStmt->bind_param("i", $user_id);

        if (!$instructorStmt->execute()) {
            echo json_encode(["success" => false, "message" => "Error inserting user into instructors table: " . $instructorStmt->error]);
            exit();
        }

        $instructorStmt->close();

        $payload = [
            "iat" => time(),
            "nbf" => time(),
            "exp" => time() + 3600,
            "data" => [
                "user_id" => $user_id,
                "username" => $username,
                "user_type" => "instructor"
            ]
        ];

        $jwt = JWT::encode($payload, $secretKey, 'HS256');

        echo json_encode([
            "success" => true,
            "message" => "Instructor created and logged in successfully",
            "user_id" => $user_id,
            "token" => $jwt
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
