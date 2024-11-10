<?php
// Start the session and set headers for CORS and content type
session_start();

// Set CORS headers (This should be placed before any HTML or other output)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

// Include database connection file
include('db_connection.php');

// Fetch user data from the database
$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session after login

$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// If form is submitted, process the form data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect the form data
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $location = $_POST['location'];
    $dob = $_POST['dob'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $target_weight = $_POST['target_weight'];
    $ideal_bmi = $_POST['ideal_bmi'];

    // Profile picture upload handling
    $image = $_FILES['profile_image']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a valid image
    if (getimagesize($_FILES['profile_image']['tmp_name']) === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size (limit to 5MB)
    if ($_FILES['profile_image']['size'] > 5000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // If everything is fine, try to upload the file
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            // Update profile in the database
            $update_query = "UPDATE users SET full_name = ?, username = ?, email = ?, phone_number = ?, location = ?, dob = ?, height = ?, weight = ?, target_weight = ?, ideal_bmi = ?, profile_image = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sssssssssdsi", $full_name, $username, $email, $phone_number, $location, $dob, $height, $weight, $target_weight, $ideal_bmi, $target_file, $user_id);
            $stmt->execute();
            echo "Profile updated successfully.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>
