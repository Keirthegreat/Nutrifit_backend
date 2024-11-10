<?php
// Allow requests from the specific origin where your frontend is hosted
header("Access-Control-Allow-Origin: http://127.0.0.1:3000"); // Replace with your frontend URL in production
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Allow preflight OPTIONS requests (required by some browsers)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'db.php'; // Include the database connection

// Set a static user ID for testing purposes
$userId = 1; // Replace with a valid user_id from your database for testing

// Fetch current user profile data from the profiles table
$stmt = $conn->prepare("SELECT * FROM profiles WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$userProfile = $stmt->fetch(PDO::FETCH_ASSOC);

// If no profile data found, show an error
if (!$userProfile) {
    echo json_encode(["error" => "Profile not found."]);
    exit();
}

// Handle the form submission and update profile data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect data from the form
    $fullName = $_POST['full_name'] ?? null;
    $username = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;
    $phoneNumber = $_POST['phone_number'] ?? null;
    $location = $_POST['location'] ?? null;
    $dob = $_POST['dob'] ?? null;
    $height = $_POST['height'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $targetWeight = $_POST['target_weight'] ?? null;
    $idealBmi = $_POST['ideal_bmi'] ?? null;
    $facebookLink = $_POST['facebook_link'] ?? null;
    $twitterLink = $_POST['twitter_link'] ?? null;
    $instagramLink = $_POST['instagram_link'] ?? null;

    // Debug: Check if form data is received
    echo json_encode(["debug" => "Received form data", "data" => $_POST]);

    // Handle the file upload
    $profileImage = null; // Default to null in case no image is uploaded
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $uploadDir = "/path/to/your/upload/directory/"; // Set to a valid, writable path
        $uploadFile = $uploadDir . basename($_FILES['profile_image']['name']);

        if (!is_writable($uploadDir)) {
            echo json_encode(["error" => "Upload directory is not writable."]);
            exit();
        }

        $fileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
        if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadFile)) {
                $profileImage = $uploadFile;
            } else {
                echo json_encode(["error" => "Failed to move uploaded file."]);
                exit();
            }
        } else {
            echo json_encode(["error" => "Invalid file type."]);
            exit();
        }
    } else {
        echo json_encode(["debug" => "File upload error or no file uploaded."]);
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
    $stmtUpdate->bindParam(':profile_image', $profileImage);
    $stmtUpdate->bindParam(':user_id', $userId);

    if ($stmtUpdate->execute()) {
        echo json_encode(["success" => true, "message" => "Profile updated successfully.", "profile_image" => $profileImage]);
    } else {
        echo json_encode(["error" => "Failed to update profile."]);
    }
}
?>

