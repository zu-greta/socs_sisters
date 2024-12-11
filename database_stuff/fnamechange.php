<?php
session_start();
$userID = $_SESSION['user_id'];
// try {
//     $database = new PDO('sqlite:ssDB.sq3');
//     $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     $stmt = $database->prepare("SELECT * FROM Users WHERE user_id = ?");
//     $stmt->execute([$userID]);
//     $userinfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
//     if (count($userinfo) === 0) {
//         echo json_encode(['success' => false, 'error' => 'User not found']);
//         exit;
//     }
//     $userFname = $userinfo[0]['fname'];
// } catch (PDOException $e) {
//     echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
//     exit;
// }

// NO IDEA IF THIS WORKS

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate the form data
    $fn = $_POST['fn'] ?? '';
    $ln = $_POST['ln'] ?? '';
    
    
    if (empty($fn) || empty($ln)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields']);
        exit;
    }

    // Insert each slot into the database
    try {
        $database = new PDO('sqlite:ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $database->prepare(
            "UPDATE Users SET fname = ? WHERE user_id = ?"
        );
        $stmt->execute([$fn, $userID]);

        $stmt = $database->prepare(
            "UPDATE Users SET lname = ? WHERE user_id = ?"
        );
        $stmt->execute([$ln, $userID]);

        $response = [
            "success" => true,
        ];

        // Send JSON response
        echo json_encode($response);
        //redirect back to the dashboard
        header('Location: ../frontend_stuff/preferences.html');
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

?>