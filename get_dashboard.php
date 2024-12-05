<?php
header("Access-Control-Allow-Origin: *"); // Allows all origins. For production, replace '*' with a specific domain.
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Specifies allowed HTTP methods.
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // Allow custom headers.
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

try {
    // Database credentials
    $uri = 'postgresql://your_database_user:your_password@your_database_host:your_port/your_database_name'; // Update with actual credentials
    $db = parse_url($uri);
    $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname=" . ltrim($db['path'], '/');
    $conn = new PDO($dsn, $db['user'], $db['pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get user_id from query parameter
    $user_id = $_GET['user_id'] ?? null;

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
        exit;
    }

    // Prepare and execute SQL query
    $stmt = $conn->prepare("SELECT user_id, current_bmi, calories_consumed, Target, updated_at FROM dashboard WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $dashboardData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if data exists
    if ($dashboardData) {
        echo json_encode(['status' => 'success', 'data' => $dashboardData]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Dashboard data not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

