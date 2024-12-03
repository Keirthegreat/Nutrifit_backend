<?php
// Add CORS headers
header("Access-Control-Allow-Origin: http://127.0.0.1:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Start the session
session_start();
include 'db.php';

$response = []; // Initialize response

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $response = [
        'status' => 'error',
        'message' => 'User not logged in.'
    ];
    echo json_encode($response);
    exit();
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id']; // Get user_id from session

    // Validate and sanitize inputs
    $fullName = htmlspecialchars($_POST['fullName'], ENT_QUOTES, 'UTF-8');
    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = $_POST['phone'];
    $location = $_POST['location'];
    $dob = $_POST['dob'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $targetWeight = $_POST['targetWeight'];
    $idealBmi = $_POST['idealBmi'];
    $badge = $_POST['badge'];
    $facebook = $_POST['facebook'];
    $twitter = $_POST['twitter'];
    $instagram = $_POST['instagram'];

    // Handle profile picture upload
    $profilePicture = null;
    $uploadDir = 'uploads/';
    if (!empty($_FILES['profilePicture']['name'])) {
        // Ensure upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $profilePicture = $uploadDir . basename($_FILES['profilePicture']['name']);
        move_uploaded_file($_FILES['profilePicture']['tmp_name'], $profilePicture);
    }

    try {
        // Insert or update profile data based on user_id
        $stmt = $conn->prepare("INSERT INTO profiles 
            (user_id, full_name, username, email, phone, location, dob, height, weight, target_weight, ideal_bmi, badge, facebook, twitter, instagram, profile_picture)
            VALUES 
            (:user_id, :fullName, :username, :email, :phone, :location, :dob, :height, :weight, :targetWeight, :idealBmi, :badge, :facebook, :twitter, :instagram, :profilePicture)
            ON DUPLICATE KEY UPDATE
            full_name = :fullName, username = :username, email = :email, phone = :phone, location = :location, dob = :dob, height = :height, weight = :weight, 
            target_weight = :targetWeight, ideal_bmi = :idealBmi, badge = :badge, facebook = :facebook, twitter = :twitter, instagram = :instagram, 
            profile_picture = :profilePicture");

        $stmt->execute([
            ':user_id' => $user_id,
            ':fullName' => $fullName,
            ':username' => $username,
            ':email' => $email,
            ':phone' => $phone,
            ':location' => $location,
            ':dob' => $dob,
            ':height' => $height,
            ':weight' => $weight,
            ':targetWeight' => $targetWeight,
            ':idealBmi' => $idealBmi,
            ':badge' => $badge,
            ':facebook' => $facebook,
            ':twitter' => $twitter,
            ':instagram' => $instagram,
            ':profilePicture' => $profilePicture
        ]);

        $response = [
            'status' => 'success',
            'message' => 'Profile saved successfully.'
        ];
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        $response = [
            'status' => 'error',
            'message' => 'An error occurred while saving the profile. Please try again later.'
        ];
    }
} else {
    // Handle invalid request methods
    $response = [
        'status' => 'error',
        'message' => 'Invalid request method.'
    ];
}

// Return JSON response
echo json_encode($response);
?>
