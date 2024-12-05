<?php
// Start the session at the very beginning of the file
session_start();

// Debugging: Check if a session has started and if the user ID exists
if (session_status() === PHP_SESSION_ACTIVE) {
    if (isset($_SESSION['user_id'])) {
        error_log("Session is active. User ID: " . $_SESSION['user_id']);
    } else {
        error_log("Session is active but no user is logged in.");
    }
} else {
    error_log("Session is not active.");
}

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

include 'db.php'; // Include your database connection

$response = []; // Initialize response array

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;

    // Validate required fields
    if (!$username || !$password) {
        $response = [
            'status' => 'error',
            'message' => 'Username and password are required.'
        ];
        echo json_encode($response);
        exit();
    }

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

            // Debugging: Log session variables
            error_log("User logged in successfully. Session user_id: " . $_SESSION['user_id']);

            $response = [
                'status' => 'success',
                'message' => 'Login successful!',
                'user_id' => $user['id'], // Ensure user_id is sent
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
?>
