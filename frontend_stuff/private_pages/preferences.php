<?php
session_start();
//GRETA ZU
if(!isset($_COOKIE['auth_key'])){
    header("Location: login");
    exit;
}
try {
    // Connect to the database
    $database = new PDO('sqlite:./database_stuff/ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the secret key exists in the database
    $stmt = $database->prepare("SELECT * FROM Sessions WHERE session_token = ?");
    $stmt->execute([$_COOKIE['auth_key']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Secret key not valid, redirect to login
        header("Location: login");
        exit;
    }
    //else user is logged in
    //redirect to dashboard if already logged in
    include './frontend_stuff/preferences.html';  
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

?>