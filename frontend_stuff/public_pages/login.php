<?php
session_start();
//TODO login checking - TEMPORARY RN
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    echo "Email: $email, Password: $password";

    try {
        // Connect to the database
        $database = new PDO('sqlite:ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch user data
        $stmt = $database->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Generate a secret key
            $secretKey = bin2hex(random_bytes(16));

            // Save secret key in DB
            $stmt = $database->prepare("INSERT INTO Sessions (user_id, session_token) VALUES (?, ?)");
            $stmt->execute([$user['user_id'], $secretKey]);

            // Save the key in a cookie
            setcookie("auth_key", $secretKey, [
                'expires' => time() + 3600, // 1 hour
                'httponly' => true,         // Prevent JavaScript access
                'secure' => false,           // Use HTTP
                'samesite' => 'Strict',     // Prevent cross-site request forgery
            ]);

            // Redirect to private page
            //redirect to dashboard if already logged in
            include './frontend_stuff/dashboard.html';  
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

include './frontend_stuff/login.html';
?>
