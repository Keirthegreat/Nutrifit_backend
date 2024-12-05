<?php
// Headers to allow CORS and JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Database connection URI
$uri = 'postgresql://postgres.dsoafkhbxwxhzvgivbxh:Keirsteph@12@aws-0-ap-southeast-1.pooler.supabase.com:6543/postgres';

try {
    // Parse the URI to extract connection details
    $parsedUri = parse_url($uri);
    $host = $parsedUri['host'];
    $port = $parsedUri['port'];
    $db_name = ltrim($parsedUri['path'], '/');
    $username = $parsedUri['user'];
    $password = $parsedUri['pass'];

    // Establish connection using PDO
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db_name", $username, $password);
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
    // Fetch data from the DASHBOARD table (case-sensitive)
    $stmt = $conn->prepare('SELECT current_bmi, calories_consumed, Target, updated_at FROM "DASHBOARD" WHERE user_id = :user_id');
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
