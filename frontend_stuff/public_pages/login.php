<?php
session_start();
//GRETA ZU
$redirect = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : 'dashboard';
//if redirect is from makeTimeRequest, change the url from makeTimeRequest?token= to makeTimeRequest#token=
if (strpos($redirect, 'makeTimeRequest') !== false) {
    //for mimi since it doesnt take %3F...
    $re = str_replace('makeTimeRequest#', 'makeTimeRequest?', $redirect);
}
if ($redirect == 'dashboard') {
    $re = 'dashboard';
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];


    try {
        // Connect to the database
        $database = new PDO('sqlite:./database_stuff/ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch user data
        $stmt = $database->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
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

            header("Location: " . $re);
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
