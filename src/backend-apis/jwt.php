<?php
use Firebase\JWT\JWT;

function createJWT($user_id, $secretKey) {
    $issuedAt = time();
    $expirationTime = $issuedAt + 3600; // Token valid for 1 hour

    $payload = [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'data' => [
            'user_id' => $user_id
        ]
    ];

    return JWT::encode($payload, $secretKey, 'HS256');
}

function validateJWT($token, $secretKey) {
    try {
        $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key($secretKey, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        return false;
    }
}