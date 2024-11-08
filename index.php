<?php
// CORS headers at the top of the file
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
include 'db.php'; // Include your database connection

$response = []; // Initialize response array

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Prepare and execute the query to fetch user by username
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user exists and verify password
        if ($user && password_verify($password, $user['password_hash'])) {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $response = [
                'status' => 'success',
                'message' => 'Login successful!',
                'user_id' => $user['id'],
                'username' => $user['username']
            ];
        } else {
            // Invalid credentials
            $response = [
                'status' => 'error',
                'message' => 'Invalid username or password.'
            ];
        }
    } catch (PDOException $e) {
        // Database error
        $response = [
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
} else {
    // Invalid request method
    $response = [
        'status' => 'error',
        'message' => 'Invalid request method.'
    ];
}

// Return JSON response
echo json_encode($response);
