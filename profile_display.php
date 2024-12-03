<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

session_start(); // Start session
include 'db.php'; // Include database connection

$response = []; // Initialize response

if (!isset($_SESSION['user_id'])) {
    // Check if the user is logged in
    $response = [
        'status' => 'error',
        'message' => 'User not logged in.'
    ];
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $user_id = $_SESSION['user_id']; // Get user_id from session

    try {
        // Fetch profile data for the logged-in user
        $stmt = $conn->prepare("SELECT * FROM profiles WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($profile) {
            $response = [
                'status' => 'success',
                'data' => $profile
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Profile not found.'
            ];
        }
    } catch (PDOException $e) {
        $response = [
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Invalid request method.'
    ];
}

echo json_encode($response);
?>
