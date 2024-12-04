<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

try {
    $uri = 'postgresql://postgres.dsoafkhbxwxhzvgivbxh:Keirsteph@12@aws-0-ap-southeast-1.pooler.supabase.com:6543/postgres'; // Update with your database credentials
    $db = parse_url($uri);
    $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname=" . ltrim($db['path'], '/');
    $conn = new PDO($dsn, $db['user'], $db['pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_GET['user_id'] ?? null;

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM \"PROFILES\" WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile) {
        echo json_encode(['status' => 'success', 'data' => $profile]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Profile not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
