<?php

require "../../app/init.php";

// Send secure headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Access-Control-Allow-Methods: GET");

// Optionally add CORS header if required
// header("Access-Control-Allow-Origin: https://yourdomain.com");

try {

    $data = $DB->SELECT('users', 'id, user_id, firstname, lastname, username, email, email_verified_at, address, contact, age, gender, image, role, position, status, created_at, updated_at, last_login, password');

    $response = [
        'success' => true,
        'count' => count($data),
        'data' => $data,
        'timestamp' => time()
    ];

    http_response_code(200);
    echo json_encode($response);
} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal Server Error',
        'error' => $e->getMessage()
    ]);
} finally {

    $DB->CLOSE();
}
