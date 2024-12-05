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
    $uri = 'postgresql://postgres.dsoafkhbxwxhzvgivbxh:Keirsteph@12@aws-0-ap-southeast-1.pooler.supabase.com:6543/postgres'; // Replace with actual credentials
    $db = parse_url($uri);

    // Parse the URI and validate credentials
    if (!isset($db['host'], $db['port'], $db['user'], $db['pass'], $db['path'])) {
        throw new Exception("Invalid database credentials. Check the connection URI.");
    }

    $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname=" . ltrim($db['path'], '/');
    $conn = new PDO($dsn, $db['user'], $db['pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get user_id from query parameter and validate
    $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);

    if (!$user_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid or missing User ID.'
        ]);
        http_response_code(400); // Bad Request
        exit;
    }

    // Prepare and execute SQL query
    $stmt = $conn->prepare("SELECT user_id, current_bmi, calories_consumed, Target, updated_at FROM dashboard WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $dashboardData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if data exists
    if ($dashboardData) {
        echo json_encode([
            'status' => 'success',
            'data' => $dashboardData
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Dashboard data not found for the given User ID.'
        ]);
        http_response_code(404); // Not Found
    }
} catch (PDOException $e) {
    // Catch database connection or query errors
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    http_response_code(500); // Internal Server Error
} catch (Exception $e) {
    // Catch any general errors
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    http_response_code(500); // Internal Server Error
}
?>
