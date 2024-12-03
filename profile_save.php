<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

session_start(); // Start the session

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id']; // Get user_id from session

    $fullName = $_POST['fullName'];
    $username = $_POST['username'];
    $email = $_POST['email'];
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
    if (!empty($_FILES['profilePicture']['name'])) {
        $uploadDir = 'uploads/';
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
