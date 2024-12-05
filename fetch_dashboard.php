<?php
// Headers to allow CORS and JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Database connection
$host = 'localhost'; // Update with your DB host
$db_name = 'your_database'; // Update with your DB name
$username = 'your_user'; // Update with your DB user
$password = 'your_password'; // Update with your DB password

try {
    $conn = new PDO("pgsql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Validate user_id from query parameter
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing user_id']);
    exit();
}

try {
    // Fetch data from the table
    $stmt = $conn->prepare("SELECT current_bmi, calories_consumed, Target, updated_at FROM your_table_name WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Check if data exists for the user
    if ($stmt->rowCount() > 0) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $result]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No data found for the given user_id']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Query failed: ' . $e->getMessage()]);
}
