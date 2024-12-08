<?php
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session to log the user out
session_destroy();

// Clear the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}                               

// Redirect to the login page
header("Location: ../frontend_stuff/landing.html");
exit;
?>