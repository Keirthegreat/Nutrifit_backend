<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

session_start();
include 'db.php'; // Include your database connection

// Handle OPTIONS request
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

// Determine request method
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET" && isset($_GET["endpoint"])) {
    $endpoint = $_GET["endpoint"];
    $userId = $_GET["userId"] ?? null;

    switch ($endpoint) {
        case "dashboard":
            if ($userId) {
                $stmt = $conn->prepare("SELECT current_bmi, ideal_bmi, current_calories FROM users WHERE id = :id");
                $stmt->bindParam(':id', $userId);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($result);
            } else {
                echo json_encode(["message" => "User ID is required"]);
            }
            break;

        default:
            echo json_encode(["message" => "Invalid endpoint"]);
            break;
    }
} elseif ($method === "POST") {
    if (!empty($_FILES['profile_image']['tmp_name'])) {
        // Handle file upload if profile_image is included
        $uploadDir = 'uploads/';
        $profileImagePath = $uploadDir . basename($_FILES['profile_image']['name']);
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $profileImagePath);
    }

    $input = json_decode(file_get_contents("php://input"), true) ?: $_POST; // Support both JSON and form-data
    $endpoint = $input["endpoint"] ?? null;

    switch ($endpoint) {
        case "profile_save":
            $userId = $input["userId"];
            $username = $input["username"];
            $ideal_bmi = $input["ideal_bmi"];
            $full_name = $input["full_name"];
            $email = $input["email"];
            $phone_number = $input["phone_number"];
            $location = $input["location"];
            $dob = $input["dob"];
            $height = $input["height"];
            $weight = $input["weight"];
            $target_weight = $input["target_weight"];
            $facebook = $input["facebook"];
            $twitter = $input["twitter"];
            $instagram = $input["instagram"];

            $stmt = $conn->prepare("
                UPDATE users 
                SET username = :username,
                    ideal_bmi = :ideal_bmi,
                    full_name = :full_name,
                    email = :email,
                    phone_number = :phone_number,
                    location = :location,
                    dob = :dob,
                    height = :height,
                    weight = :weight,
                    target_weight = :target_weight,
                    facebook = :facebook,
                    twitter = :twitter,
                    instagram = :instagram,
                    profile_image = :profile_image
                WHERE id = :id
            ");
            $stmt->execute([
                ':username' => $username,
                ':ideal_bmi' => $ideal_bmi,
                ':full_name' => $full_name,
                ':email' => $email,
                ':phone_number' => $phone_number,
                ':location' => $location,
                ':dob' => $dob,
                ':height' => $height,
                ':weight' => $weight,
                ':target_weight' => $target_weight,
                ':facebook' => $facebook,
                ':twitter' => $twitter,
                ':instagram' => $instagram,
                ':profile_image' => $profileImagePath ?? null,
                ':id' => $userId,
            ]);

            echo json_encode(["message" => "Profile updated successfully!"]);
            break;

        case "bmi_update":
            $userId = $input["userId"];
            $current_bmi = $input["current_bmi"];

            $stmt = $conn->prepare("UPDATE users SET current_bmi = :current_bmi WHERE id = :id");
            $stmt->bindParam(':current_bmi', $current_bmi);
            $stmt->bindParam(':id', $userId);

            if ($stmt->execute()) {
                echo json_encode(["message" => "BMI updated successfully!"]);
            } else {
                echo json_encode(["message" => "Failed to update BMI"]);
            }
            break;

        case "calories_update":
            $userId = $input["userId"];
            $current_calories = $input["current_calories"];
            $target_calories = $input["target_calories"];

            $stmt = $conn->prepare("UPDATE users SET current_calories = :current_calories, target_calories = :target_calories WHERE id = :id");
            $stmt->bindParam(':current_calories', $current_calories);
            $stmt->bindParam(':target_calories', $target_calories);
            $stmt->bindParam(':id', $userId);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Calories updated successfully!"]);
            } else {
                echo json_encode(["message" => "Failed to update calories"]);
            }
            break;

        default:
            echo json_encode(["message" => "Invalid endpoint"]);
            break;
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}
?>
