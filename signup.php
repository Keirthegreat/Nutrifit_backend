<?php
include 'db.php'; // Include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect user input
    $fullName = $_POST['full_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password for security
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Check if the username or email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "Username or email already taken.";
        } else {
            // Insert new user into the database
            $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password_hash) VALUES (:fullName, :username, :email, :passwordHash)");
            $stmt->bindParam(':fullName', $fullName);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':passwordHash', $passwordHash);
            $stmt->execute();

            echo "Signup successful!";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
