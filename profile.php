<?php
session_start();
include 'db.php'; // Include your database connection

// Set CORS headers to allow cross-origin requests (optional)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

$userId = $_SESSION['user_id']; // Assuming the user is logged in

// Fetch profile data from the database
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// If form is submitted, update profile data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect POST data
    $full_name = $_POST['full_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $location = $_POST['location'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $height = $_POST['height'] ?? '';
    $weight = $_POST['weight'] ?? '';
    $target_weight = $_POST['target_weight'] ?? '';
    $ideal_bmi = $_POST['ideal_bmi'] ?? '';
    $facebook = $_POST['facebook'] ?? '';
    $twitter = $_POST['twitter'] ?? '';
    $instagram = $_POST['instagram'] ?? '';

    // Handle file upload to Supabase Storage
    $image_url = ''; // Default to empty if no image uploaded
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
        $supabase_url = 'https://dsoafkhbxwxhzvgivbxh.supabase.co/storage/v1/object/';
        $bucket_name = 'your_bucket_name';
        $supabase_api_key = 'your_supabase_api_key';

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
    }

    // Update profile in the database
    $sql = "UPDATE users SET
            full_name = ?, username = ?, email = ?, phone_number = ?, location = ?, dob = ?, 
            height = ?, weight = ?, target_weight = ?, ideal_bmi = ?, 
            facebook = ?, twitter = ?, instagram = ?, profile_image = ?
            WHERE user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssssi", $full_name, $username, $email, $phone_number, $location, $dob, $height, $weight, 
                     $target_weight, $ideal_bmi, $facebook, $twitter, $instagram, $image_url, $userId);
    $result = $stmt->execute();

    if ($result) {
        echo json_encode([
            "success" => true,
            "full_name" => $full_name,
            "role" => 'Health Enthusiast', // Modify based on your logic for role
            "email" => $email,
            "phone_number" => $phone_number,
            "location" => $location,
            "dob" => $dob,
            "height" => $height,
            "weight" => $weight,
            "target_weight" => $target_weight,
            "ideal_bmi" => $ideal_bmi,
            "profile_image_url" => $image_url // Send the Supabase file URL back to the frontend
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update profile."]);
    }
}

// Close database connection
$stmt->close();
$conn->close();
?>
