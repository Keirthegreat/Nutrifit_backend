<?php
// Add headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Connection to PostgreSQL
$uri = 'postgresql://postgres.dsoafkhbxwxhzvgivbxh:Keirsteph@12@aws-0-ap-southeast-1.pooler.supabase.com:6543/postgres'; // Update with your database credentials

try {
    $db = parse_url($uri);
    $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname=" . ltrim($db['path'], '/');
    $conn = new PDO($dsn, $db['user'], $db['pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read the incoming JSON data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (
        empty($data['user_id']) || 
        empty($data['full_name']) || 
        empty($data['username']) || 
        empty($data['email'])
    ) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Required fields are missing.'
        ]);
        exit;
    }

    // Prepare SQL to insert or update profile
    $sql = "
        INSERT INTO \"PROFILES\" (
            user_id, full_name, username, email, phone_number, location, 
            date_of_birth, height_cm, weight_kg, target_weight, ideal_bmi, role, social_links, profile_picture_url, updated_at
        ) VALUES (
            :user_id, :full_name, :username, :email, :phone_number, :location, 
            :date_of_birth, :height_cm, :weight_kg, :target_weight, :ideal_bmi, :role, :social_links, :profile_picture_url, NOW()
        )
        ON CONFLICT (user_id) DO UPDATE SET
            full_name = EXCLUDED.full_name,
            username = EXCLUDED.username,
            email = EXCLUDED.email,
            phone_number = EXCLUDED.phone_number,
            location = EXCLUDED.location,
            date_of_birth = EXCLUDED.date_of_birth,
            height_cm = EXCLUDED.height_cm,
            weight_kg = EXCLUDED.weight_kg,
            target_weight = EXCLUDED.target_weight,
            ideal_bmi = EXCLUDED.ideal_bmi,
            role = EXCLUDED.role,
            social_links = EXCLUDED.social_links,
            profile_picture_url = EXCLUDED.profile_picture_url,
            updated_at = NOW();
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':user_id' => $data['user_id'],
        ':full_name' => $data['full_name'],
        ':username' => $data['username'],
        ':email' => $data['email'],
        ':phone_number' => $data['phone_number'] ?? null,
        ':location' => $data['location'] ?? null,
        ':date_of_birth' => $data['date_of_birth'] ?? null,
        ':height_cm' => $data['height_cm'] ?? null,
        ':weight_kg' => $data['weight_kg'] ?? null,
        ':target_weight' => $data['target_weight'] ?? null,
        ':ideal_bmi' => $data['ideal_bmi'] ?? null,
        ':role' => $data['role'] ?? null,
        ':social_links' => json_encode($data['social_links'] ?? []),
        ':profile_picture_url' => $data['profile_picture_url'] ?? null,
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Profile saved successfully.'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
