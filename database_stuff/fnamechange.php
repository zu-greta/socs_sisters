<?php
session_start();
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

        // Get userID
        $stmt = $database->prepare("SELECT user_id FROM Sessions WHERE session_token = ?");
        $stmt->execute([$_COOKIE['auth_key']]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$session) {
            header("Location: login");
        }
        $userID = $session['user_id'];

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
        header('Location: ../preferences');
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

?>