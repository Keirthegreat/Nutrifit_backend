<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

session_start();
include 'db.php'; // Include database connection

// Handle OPTIONS requests
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

// Validate database connection
if (!isset($conn)) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection not established", "data" => null]);
    exit();
}

try {
    // Determine request method
    $method = $_SERVER["REQUEST_METHOD"];

    if ($method === "GET" && isset($_GET["endpoint"])) {
        $endpoint = $_GET["endpoint"];
        $userId = $_GET["userId"] ?? null;

        if ($endpoint === "dashboard") {
            if ($userId) {
                // Fetch dashboard data
                $stmt = $conn->prepare("SELECT current_bmi, ideal_bmi, current_calories FROM users WHERE id = :id");
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode([
                    "message" => "Data retrieved successfully",
                    "data" => $result ?: []
                ]);
            } else {
                echo json_encode(["message" => "User ID is required", "data" => null]);
            }
        } else {
            echo json_encode(["message" => "Invalid endpoint", "data" => null]);
        }
    } elseif ($method === "POST") {
        $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
        $endpoint = $input["endpoint"] ?? null;

        if ($endpoint === "profile_save") {
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

            // Handle optional profile image
            $profileImagePath = null;
            if (!empty($_FILES['profile_image']['tmp_name'])) {
                $uploadDir = 'uploads/';
                $profileImagePath = $uploadDir . basename($_FILES['profile_image']['name']);
                if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $profileImagePath)) {
                    echo json_encode(["message" => "Failed to upload profile image", "data" => null]);
                    exit();
                }
            }

            // Update profile data
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
                ':profile_image' => $profileImagePath,
                ':id' => $userId,
            ]);

            // Fetch updated profile data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $updatedData = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                "message" => "Profile updated successfully!",
                "data" => $updatedData ?: []
            ]);
        } elseif ($endpoint === "bmi_update") {
            $userId = $input["userId"];
            $current_bmi = $input["current_bmi"];

            $stmt = $conn->prepare("UPDATE users SET current_bmi = :current_bmi WHERE id = :id");
            $stmt->bindParam(':current_bmi', $current_bmi, PDO::PARAM_STR);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo json_encode([
                    "message" => "BMI updated successfully!",
                    "data" => ["current_bmi" => $current_bmi]
                ]);
            } else {
                echo json_encode(["message" => "Failed to update BMI", "data" => null]);
            }
        } elseif ($endpoint === "calories_update") {
            $userId = $input["userId"];
            $current_calories = $input["current_calories"];
            $target_calories = $input["target_calories"];

            $stmt = $conn->prepare("UPDATE users SET current_calories = :current_calories, target_calories = :target_calories WHERE id = :id");
            $stmt->bindParam(':current_calories', $current_calories, PDO::PARAM_INT);
            $stmt->bindParam(':target_calories', $target_calories, PDO::PARAM_INT);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo json_encode([
                    "message" => "Calories updated successfully!",
                    "data" => ["current_calories" => $current_calories, "target_calories" => $target_calories]
                ]);
            } else {
                echo json_encode(["message" => "Failed to update calories", "data" => null]);
            }
        } else {
            echo json_encode(["message" => "Invalid endpoint", "data" => null]);
        }
    } else {
        echo json_encode(["message" => "Invalid request method", "data" => null]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "An error occurred", "error" => $e->getMessage(), "data" => null]);
}

