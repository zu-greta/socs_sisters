<?php
session_start();
$userID = $_SESSION['user_id'];
try {
    $database = new PDO('sqlite:ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $database->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->execute([$userID]);
    $userinfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($userinfo) === 0) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    $userEmail = $userinfo[0]['email'];
    $username = $userinfo[0]['username'];
    $userFname = $userinfo[0]['fname'];
    $userLname = $userinfo[0]['lname'];
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
// the pop up message modal should be displayed now over the schedule page
$userDetails = [
    "username" => $username,
    "userEmail" => $userEmail,
    "userFname" => $userFname,
    "userLname" => $userLname,
];

$response = [
    "success" => true,
    "userDetails" => $userDetails,
];

// Send JSON response
echo json_encode($response);
exit;

// CREATE TABLE Users (
//     user_id INTEGER PRIMARY KEY AUTOINCREMENT, -- Primary Key
//     username TEXT UNIQUE NOT NULL,
//     fname TEXT NOT NULL,
//     lname TEXT NOT NULL,
//     email TEXT UNIQUE NOT NULL,
//     password_hash TEXT NOT NULL, -- Placeholder for storing passwords
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     last_modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// );

?>