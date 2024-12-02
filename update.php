<?php
session_start();
include 'db.php'; // Include your database connection

// Set CORS headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

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
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $profile_image = $_FILES['profile_image'];
        $image_path = $profile_image['tmp_name'];
        $image_name = basename($profile_image['name']);

        // Prepare the request to Supabase storage bucket
        $supabase_url = 'https://dsoafkhbxwxhzvgivbxh.supabase.co/storage/v1/object/'; // Your Supabase URL
        $bucket_name = 'your_bucket_name'; // Replace with your actual bucket name
        $supabase_api_key = 'your_supabase_api_key'; // Replace with your Supabase API key

        // Initialize the cURL request to upload the file to Supabase
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $supabase_url . $bucket_name . '/' . $image_name); // Object path for storage
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
        $image_url = ''; // No image uploaded
    }

    // Insert or update profile in the database
    $sql = "UPDATE users SET
            full_name = ?, username = ?, email = ?, phone_number = ?, location = ?, dob = ?, 
            height = ?, weight = ?, target_weight = ?, ideal_bmi = ?, 
            facebook = ?, twitter = ?, instagram = ?, profile_image = ?
            WHERE user_id = ?"; // assuming 'user_id' is the session user ID

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssssi", $full_name, $username, $email, $phone_number, $location, $dob, $height, $weight, 
                     $target_weight, $ideal_bmi, $facebook, $twitter, $instagram, $image_url, $_SESSION['user_id']);
    $result = $stmt->execute();

    if ($result) {
        // Send response back to the frontend
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
?>
