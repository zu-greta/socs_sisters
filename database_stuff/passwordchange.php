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
    $userPassword = $userinfo[0]['password_hash'];
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// NO IDEA IF THIS WORKS

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate the form data
    $currentPassword = $_POST['current-password'] ?? '';
    $newPassword = $_POST['new-password'] ?? '';
    $confirmPassword = $_POST['reenter-new-password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields']);
        exit;
    }
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
        exit;
    }

    if (!password_verify($currentPassword, $userPassword)) {
        echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
        exit;
    }

    // Insert each slot into the database
    try {
        $database = new PDO('sqlite:ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        
        // Prepare the insert statement
        $stmt = $database->prepare(
            "UPDATE Users SET password_hash = ? WHERE user_id = ?"
        );

        // Execute the statement
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userID]);

        $response = [
            "success" => true,
        ];

        // Send JSON response
        echo json_encode($response);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

?>