<?php
session_start();

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'dashboard';
//if redirect is from makeTimeRequest, change the url from makeTimeRequest?token= to makeTimeRequest#token=
if (strpos($redirect, 'makeTimeRequest') !== false) {
    $redirect = str_replace('makeTimeRequest#', 'makeTimeRequest?', $redirect);
}
//echo "Redirect: $redirect";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];


    try {
        //echo "Trying to connect to the database";
        // Connect to the database
        $database = new PDO('sqlite:./database_stuff/ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //echo "Connected to the database";

        // Fetch user data
        $stmt = $database->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // echo "User fetched";

        if ($user && password_verify($password, $user['password_hash'])) {
            // Generate a secret key
            $secretKey = bin2hex(random_bytes(16));
            // echo "Secret key generated";
            // echo "Secret key: $secretKey";

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

            //$re = html_entity_decode($redirect, ENT_QUOTES, 'UTF-8'); 
            //$re = htmlspecialchars_decode($redirect, ENT_QUOTES); 
            $re = urldecode($redirect);
            header("Location: " . $re);
            //header("Location: " . $redirect);
            exit;
        } else {
            //echo "Invalid username or password.";
            $error = "Invalid username or password.";
        }
    } catch (Exception $e) {
        //echo "Error: " . $e->getMessage();
        $error = "Error: " . $e->getMessage();
    }
}

include './frontend_stuff/login.html';
?>
