<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

include 'db.php'; // Include your updated database connection

header('Content-Type: application/json'); // Set the content type to JSON
$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Check if the username or email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Username or email already exists
            $response = [
                'status' => 'error',
                'message' => 'Username or email already exists.'
            ];
        } else {
            // Insert the new user into the database
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, username, password_hash) VALUES (:full_name, :email, :username, :password_hash)");
            $stmt->bindParam(':full_name', $fullName);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password_hash', $hashedPassword);

            if ($stmt->execute()) {
                // Signup successful
                $response = [
                    'status' => 'success',
                    'message' => 'Signup successful! Please log in.'
                ];
            } else {
                // Signup failed
                $response = [
                    'status' => 'error',
                    'message' => 'Signup failed. Please try again.'
                ];
            }
        }
    } catch (PDOException $e) {
        // Database error
        $response = [
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
} else {
    // Invalid request method
    $response = [
        'status' => 'error',
        'message' => 'Invalid request method.'
    ];
}

// Return JSON response
echo json_encode($response);
?>
