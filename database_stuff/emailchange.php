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
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// NO IDEA IF THIS WORKS

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate the form data
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields']);
        exit;
    }
    //check that this is unique in the db
    try {
        $database = new PDO('sqlite:ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $database->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $userinfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($userinfo) > 0) {
            echo json_encode(['success' => false, 'error' => 'Email already in use']);
            exit;
        }
        //else email is unique and can be updated
        else {
            // Insert each slot into the database
            try {
                $database = new PDO('sqlite:ssDB.sq3');
                $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
                
                // Prepare the insert statement
                $stmt = $database->prepare(
                    "UPDATE Users SET email = ? WHERE user_id = ?"
                );
        
                // Execute the statement
                $stmt->execute([$email, $userID]);
        
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
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

?>