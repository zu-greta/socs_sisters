<?php
session_start();
//GRETA ZU
try {
    if (isset($_COOKIE['auth_key'])) {
        $secretKey = $_COOKIE['auth_key'];

        // Connect to the database
        $database = new PDO('sqlite:./database_stuff/ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Delete the session
        $stmt = $database->prepare("DELETE FROM Sessions WHERE session_token = ?");
        $stmt->execute([$secretKey]);

        // Clear the auth_key cookie
        setcookie("auth_key", "", time() - 3600, '/');
    }
} catch (Exception $e) {
    // Log error or handle accordingly
}

header("Location: landing");
exit;
?>
