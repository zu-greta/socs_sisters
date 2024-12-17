<?php
session_start();
// $userID = $_SESSION['user_id'];
try {
    $database = new PDO('sqlite:ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Get userID
    $stmt = $database->prepare("SELECT user_id FROM Sessions WHERE session_token = ?");
    $stmt->execute([$_COOKIE['auth_key']]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$session) {
        header("Location: login");
    }
    $userID = $session['user_id'];
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate the form data
    $currentPassword = $_POST['current-password'] ?? '';
    $newPassword = $_POST['new-password'] ?? '';
    $confirmPassword = $_POST['reenter-new-password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields']);
        echo "<script>
            alert('Please fill in all required fields');
            
            window.location.href = '../preferences';
        </script>";
        exit;
    }
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
        echo "<script>
            alert('Passwords do not match');
            
            window.location.href = '../preferences';
        </script>";
        exit;
    }

    if (!password_verify($currentPassword, $userPassword)) {
        echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
        echo "<script>
            alert('Current password is incorrect');
            
            window.location.href = '../preferences';
        </script>";
        exit;
    }

    // Password validation before inserting into the database
    if (strlen($newPassword) < 10 || !preg_match('/[a-zA-Z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
        echo "<script>
            alert('Password must be at most 10 characters long, contain at least one letter, and at least one number.');
            
            window.location.href = '../preferences';
        </script>";
        exit();
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
        header('Location: ../preferences');
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

?>