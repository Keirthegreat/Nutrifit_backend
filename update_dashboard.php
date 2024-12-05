<?php
header("Access-Control-Allow-Origin: *"); // Allows all origins. Replace '*' with your domain for production.
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle POST request: Update daily goal and calories
        $input = json_decode(file_get_contents('php://input'), true);

        $user_id = $input['user_id'] ?? null;
        $calories_consumed = $input['calories_consumed'] ?? null;
        $target = $input['Target'] ?? null;

        if (!$user_id || ($calories_consumed === null && $target === null)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'User ID and at least one of Calories Consumed or Daily Goal are required.'
            ]);
            http_response_code(400); // Bad Request
            exit;
        }

        // Update the dashboard table
        $query = 'UPDATE "DASHBOARD" SET ';
        $params = [':user_id' => $user_id];
        $fields = [];

        if ($calories_consumed !== null) {
            $fields[] = '"calories_consumed" = :calories_consumed';
            $params[':calories_consumed'] = $calories_consumed;
        }

        if ($target !== null) {
            $fields[] = '"Target" = :target';
            $params[':target'] = $target;
        }

        $query .= implode(', ', $fields) . ', "updated_at" = NOW() WHERE "user_id" = :user_id';

        $stmt = $conn->prepare($query);
        $stmt->execute($params);

        echo json_encode([
            'status' => 'success',
            'message' => 'Dashboard updated successfully.'
        ]);
    } else {
        // Method not allowed
        echo json_encode([
            'status' => 'error',
            'message' => 'Method not allowed.'
        ]);
        http_response_code(405); // Method Not Allowed
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
