<?php
include "connection.php";
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

try {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (
        !isset($data['username']) || empty($data['username']) ||
        !isset($data['password']) || empty($data['password'])
    ) {
        echo json_encode(["success" => false, "message" => "Please fill in all required fields."]);
        exit();
    }

    $username = $data['username'];
    $password = $data['password'];

    $sql = "SELECT user_id , username,password, user_type , isBanned FROM users WHERE username = ?";
    $stmt = $connection->prepare($sql);

    if ($stmt === false) {
        echo json_encode(["success" => false, "message" => "Error in SQL statement: Either syntax error or database connection issue"]);
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user['isBanned'] == 1) {
            echo json_encode([
                "success" => false,
                "message" => "Your username has been banned. You are not allowed to sign in."
            ]);
            exit();
        }

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Generate JWT
            $secretKey = "zLqxD6TqRd5NV57jd8dVQj2jFK7fphgOWO/4mnCisjYX4RhWQDzxOqR4CXN0rh72IbpWoTSes3Cd6qABhT5ZSw==";
            $payload = [
                "iat" => time(), // Issued at
                "nbf" => time(), // Not before
                "exp" => time() + 3600, // Expiration (1 hour)
                "data" => [
                    "user_id" => $user['user_id'],
                    "username" => $username,
                    "user_type" => $user['user_type']
                ]
            ];

            $jwt = JWT::encode($payload, $secretKey, 'HS256');

            echo json_encode([
                "success" => true,
                "message" => "Login successful.",
                "token" => $jwt
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Invalid password."
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "User not found."
        ]);
    }

    $stmt->close();
    $connection->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Unexpected error: " . $e->getMessage()]);
}
