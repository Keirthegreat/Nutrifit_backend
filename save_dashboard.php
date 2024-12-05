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

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Handle GET request: Retrieve user data
        $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);

        if (!$user_id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid or missing User ID.'
            ]);
            http_response_code(400); // Bad Request
            exit;
        }

        $stmt = $conn->prepare("SELECT user_id, current_bmi, calories_consumed, Target, updated_at FROM dashboard WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $dashboardData = $stmt->fetch(PDO::FETCH_ASSOC);

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
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle POST request: Record BMI
        $input = json_decode(file_get_contents('php://input'), true);

        $user_id = $input['user_id'] ?? null;
        $current_bmi = $input['current_bmi'] ?? null;
        $calories_consumed = $input['calories_consumed'] ?? null;
        $target = $input['Target'] ?? null;

        if (!$user_id || !$current_bmi || !$calories_consumed || !$target) {
            echo json_encode([
                'status' => 'error',
                'message' => 'All fields (User ID, BMI, Calories Consumed, Target) are required.'
            ]);
            http_response_code(400); // Bad Request
            exit;
        }

        // Insert or update the dashboard table
        $stmt = $conn->prepare("INSERT INTO dashboard (user_id, current_bmi, calories_consumed, Target, updated_at)
                                VALUES (:user_id, :current_bmi, :calories_consumed, :target, NOW())
                                ON CONFLICT (user_id)
                                DO UPDATE SET 
                                    current_bmi = EXCLUDED.current_bmi,
                                    calories_consumed = EXCLUDED.calories_consumed,
                                    Target = EXCLUDED.Target,
                                    updated_at = NOW()");
        $stmt->execute([
            ':user_id' => $user_id,
            ':current_bmi' => $current_bmi,
            ':calories_consumed' => $calories_consumed,
            ':target' => $target
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'BMI and other data recorded successfully.'
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

