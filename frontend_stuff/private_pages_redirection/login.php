<?php
session_start();

// Simulate user credentials (in a real scenario, use a database)
// TEMPLATE CODE REPLACE WITH DB STUFF
$valid_username = "admin";
$valid_password = "passpass";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate credentials
    if ($username === $valid_username && $password === $valid_password) {
        // Set session variable TO GENEREATE A SESSION ID ????
        $_SESSION['user_id'] = 1; // Set a test user ID
        // echo "Session ID: " . session_id();
        // echo "User ID: " . $_SESSION['user_id'];
        session_write_close(); 

        // Redirect to the protected page - THIS WILL BE THE LANDING PAGE
        header("Location: schedule_sessioncheck.php");
        exit;
    } else {
        $error_message = "Invalid username or password.";
    }
}
?>

