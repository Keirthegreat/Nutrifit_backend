<?php
// Add headers for CORS and JSON response
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Connection URI from Supabase
$uri = 'postgres://postgres.dsoafkhbxwxhzvgivbxh:[YOUR-PASSWORD]@aws-0-ap-southeast-1.pooler.supabase.com:6543/postgres';

try {
    // Parse the URI into components
    $db = parse_url($uri);

    // Extract the connection details
    $host = $db['host'];
    $port = $db['port'];
    $user = $db['user'];
    $password = $db['pass'];
    $dbname = ltrim($db['path'], '/');

    // Create the DSN
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";

    // Establish the connection
    $conn = new PDO($dsn);

    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $e->getMessage()
    ]));
}
?>


