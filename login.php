<?php
include "connection.php";

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

    $sql = "SELECT user_id, password, isBanned, isAdmin FROM user WHERE username = ?";
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

    } else {
        echo json_encode([
            "success" => false,
            "message" => "User not found."
        ]);
    }

    $stmt->close();
    $connection->close();
} catch (Exception $e) {
    echo json_encode(["message" => "Unexpected error"]);
}
