<?php
// Set CORS headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");  // Allow any domain to access the resource
header("Access-Control-Allow-Methods: POST");  // Allow POST method
header("Content-Type: application/json; charset=UTF-8");  // Set content type to JSON

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Redirect to login page if not logged in
    exit();
}

include 'db.php';  // Include the database connection

$userId = $_SESSION['user_id'];  // Get logged-in user's ID

// Fetch current user profile data from the profiles table
$stmt = $conn->prepare("SELECT * FROM profiles WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$userProfile = $stmt->fetch(PDO::FETCH_ASSOC);

// If no profile data found, show an error
if (!$userProfile) {
    echo json_encode(["error" => "Profile not found."]);  // Respond with a JSON error
    exit();
}

// Handle the form submission and update profile data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect data from the form (assuming you've already prefilled the form fields)
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
    $facebookLink = $_POST['facebook_link'];
    $twitterLink = $_POST['twitter_link'];
    $instagramLink = $_POST['instagram_link'];
    
    // Handle the file upload
    $profileImage = NULL;  // Default to NULL in case no image is uploaded
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        // Set the upload directory (Make sure the folder is writable)
        $uploadDir = "/path/to/Documents/Pic_Profile/";  // Change to your desired folder path
        $uploadFile = $uploadDir . basename($_FILES['profile_image']['name']);
        
        // Check if the file is an image (optional)
        $fileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
        if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            // Move the uploaded file to the specified directory
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
                $profileImage = $uploadFile;  // Save the file path to database
            } else {
                echo json_encode(["error" => "Failed to upload image."]);
                exit();
            }
        } else {
            echo json_encode(["error" => "Invalid file type. Please upload a JPG, JPEG, PNG, or GIF file."]);
            exit();
        }
    }

    // Prepare the SQL statement to update the user's profile in the database
    $stmtUpdate = $conn->prepare("
        UPDATE profiles 
        SET full_name = :full_name, 
            username = :username, 
            email = :email, 
            phone_number = :phone_number,
            location = :location, 
            dob = :dob, 
            height = :height, 
            weight = :weight, 
            target_weight = :target_weight,
            ideal_bmi = :ideal_bmi,
            facebook_link = :facebook_link, 
            twitter_link = :twitter_link, 
            instagram_link = :instagram_link,
            profile_image = :profile_image
        WHERE user_id = :user_id
    ");

    // Bind the form data to the query parameters
    $stmtUpdate->bindParam(':full_name', $fullName);
    $stmtUpdate->bindParam(':username', $username);
    $stmtUpdate->bindParam(':email', $email);
    $stmtUpdate->bindParam(':phone_number', $phoneNumber);
    $stmtUpdate->bindParam(':location', $location);
    $stmtUpdate->bindParam(':dob', $dob);
    $stmtUpdate->bindParam(':height', $height);
    $stmtUpdate->bindParam(':weight', $weight);
    $stmtUpdate->bindParam(':target_weight', $targetWeight);
    $stmtUpdate->bindParam(':ideal_bmi', $idealBmi);
    $stmtUpdate->bindParam(':facebook_link', $facebookLink);
    $stmtUpdate->bindParam(':twitter_link', $twitterLink);
    $stmtUpdate->bindParam(':instagram_link', $instagramLink);
    $stmtUpdate->bindParam(':profile_image', $profileImage);  // For image (file path)
    $stmtUpdate->bindParam(':user_id', $userId);

    // Execute the query to update the profile
    if ($stmtUpdate->execute()) {
        // Respond with success message in JSON format
        echo json_encode(["success" => "Profile updated successfully."]);
    } else {
        // Respond with an error message in JSON format
        echo json_encode(["error" => "Failed to update profile."]);
    }
}
?>
