<?php
session_start();
include 'db.php'; // Include the database connection

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Check if the user is logged in by verifying the session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["message" => "Unauthorized. Please log in."]);
    exit;
}

// User ID is obtained from the session
$userId = $_SESSION['user_id'];

// Handle GET request to fetch the profile
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Fetch user data from the database
    $sql = "SELECT * FROM users WHERE id = $userId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            "message" => "Profile fetched successfully",
            "data" => $row // All user profile data including image
        ]);
    } else {
        echo json_encode(["message" => "Profile not found"]);
    }
}

// Handle POST request to update the profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect data from the request
    $fullName = $_POST['full_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phoneNumber = $_POST['phone_number'];
    $location = $_POST['location'];
    $dob = $_POST['dob'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $targetWeight = $_POST['target_weight'];
    $idealBmi = $_POST['ideal_bmi'];

    // Initialize the image URL to empty by default
    $image_url = ''; 

    // Handle file upload to Supabase
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $profile_image = $_FILES['profile_image'];
        $image_path = $profile_image['tmp_name'];
        $image_name = basename($profile_image['name']);
        $image_type = mime_content_type($image_path); // Check file type

        // Validate file type (only allow certain types for security)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($image_type, $allowed_types)) {
            echo json_encode(["success" => false, "message" => "Invalid image type. Only JPEG, PNG, and GIF are allowed."]);
            exit;
        }

        // Validate file size (maximum of 5MB)
        if ($profile_image['size'] > 5 * 1024 * 1024) {
            echo json_encode(["success" => false, "message" => "File size exceeds the maximum limit of 5MB."]);
            exit;
        }

        // Supabase Storage URL and API Key
        $supabase_url = 'https://dsoafkhbxwxhzvgivbxh.supabase.co';
        $bucket_name = 'Profile Images';
        $supabase_api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRzb2Fma2hieHd4aHp2Z2l2YnhoIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzMxNDI4NTIsImV4cCI6MjA0ODcxODg1Mn0.-KmKuO-nzjevx7MvnVKtm-q7kNNc4-I3y5zBH19Bqzw';

        // Initialize the cURL request to upload the file to Supabase
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $supabase_url . $bucket_name . '/' . $image_name);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $supabase_api_key,
            "Content-Type: " . $profile_image['type'],
        ]);
        curl_setopt($ch, CURLOPT_PUT, 1);
        curl_setopt($ch, CURLOPT_INFILE, fopen($image_path, 'r'));
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($image_path));

        // Execute the cURL request
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo json_encode(["success" => false, "message" => "Error uploading image to Supabase: " . curl_error($ch)]);
            exit;
        }
        curl_close($ch);

        // If upload is successful, the file URL will be the path in Supabase
        $image_url = $supabase_url . $bucket_name . '/' . $image_name;
    } else {
        // If no image uploaded, keep the old image URL from the database
        $image_url = isset($_POST['current_profile_image']) ? $_POST['current_profile_image'] : ''; // Use existing image URL
    }

    // Update the profile in the database with the image URL
    $sql = "UPDATE users SET 
            full_name = '$fullName',
            username = '$username',
            email = '$email',
            phone_number = '$phoneNumber',
            location = '$location',
            dob = '$dob',
            height = $height,
            weight = $weight,
            target_weight = $targetWeight,
            ideal_bmi = $idealBmi,
            profile_image = '$image_url'
            WHERE id = $userId";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Profile updated successfully"]);
    } else {
        echo json_encode(["message" => "Error updating profile: " . $conn->error]);
    }
}
?>
