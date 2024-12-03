<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

include 'db.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

try {
    $method = $_SERVER["REQUEST_METHOD"];
    $input = json_decode(file_get_contents("php://input"), true) ?: $_POST;

    switch ($input['action'] ?? null) {
        case 'fetch_profile':
            $userId = $input['userId'];
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(["message" => "Profile fetched", "data" => $data]);
            break;

        case 'save_profile':
            $userId = $input['userId'];

            // Handle optional profile image upload
            $profileImagePath = null;
            if (!empty($_FILES['profile_image']['tmp_name'])) {
                $uploadDir = 'uploads/';
                $profileImagePath = $uploadDir . basename($_FILES['profile_image']['name']);

                // Ensure the upload directory exists
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $profileImagePath)) {
                    echo json_encode(["message" => "Failed to upload profile image"]);
                    exit();
                }
            }

            // Update profile data
            $stmt = $conn->prepare("
                UPDATE users SET 
                    full_name = :full_name,
                    username = :username,
                    email = :email,
                    phone_number = :phone_number,
                    location = :location,
                    dob = :dob,
                    height = :height,
                    weight = :weight,
                    target_weight = :target_weight,
                    ideal_bmi = :ideal_bmi,
                    profile_image = COALESCE(:profile_image, profile_image)
                WHERE id = :id
            ");
            $stmt->execute([
                ':full_name' => $input['full_name'],
                ':username' => $input['username'],
                ':email' => $input['email'],
                ':phone_number' => $input['phone_number'],
                ':location' => $input['location'],
                ':dob' => $input['dob'],
                ':height' => $input['height'],
                ':weight' => $input['weight'],
                ':target_weight' => $input['target_weight'],
                ':ideal_bmi' => $input['ideal_bmi'],
                ':profile_image' => $profileImagePath,
                ':id' => $userId,
            ]);
            echo json_encode(["message" => "Profile updated successfully!"]);
            break;

        case 'update_bmi':
            $userId = $input['userId'];
            $stmt = $conn->prepare("UPDATE users SET current_bmi = :bmi WHERE id = :id");
            $stmt->bindParam(':bmi', $input['bmi'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(["message" => "BMI updated successfully!"]);
            break;

        case 'update_calories':
            $userId = $input['userId'];
            $stmt = $conn->prepare("UPDATE users SET current_calories = :calories, target_calories = :target WHERE id = :id");
            $stmt->bindParam(':calories', $input['current_calories'], PDO::PARAM_INT);
            $stmt->bindParam(':target', $input['target_calories'], PDO::PARAM_INT);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(["message" => "Calories updated successfully!"]);
            break;

        default:
            echo json_encode(["message" => "Invalid action"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Server error", "error" => $e->getMessage()]);
}
?>

