<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

// External URL for the database connection
$dsn = 'pgsql:host=dpg-csmsdq1u0jms73fsmieg-a.singapore-postgres.render.com;port=5432;dbname=nutrifit_db_bay8';
$username = 'nutrifit_db_bay8_user';
$password = 'iLLcdyFkYOXj5xHK2ERlWtCOlYZn8gZ4';

try {
    // Create a new PDO instance
    $conn = new PDO($dsn, $username, $password);

    // Set PDO error mode to exception for better error handling
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection errors
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit; // Stop further execution if there's a connection error
}
?>
