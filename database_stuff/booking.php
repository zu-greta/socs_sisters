<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// - be able to choose a slot and give in the name+email and submit the book (insert a booking in booking table)
// - use email cz its unique -> if email in users table, get the userid as the booked_by_id, insert into bookings
// - if not in users then leave the booked_by_id blank
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $requesterEmail = $_POST["requesterEmail"];
    $slot_id = $_POST["slot_id"];

    if (empty($requesterEmail) || empty($slot_id)) {
        // Send JSON response
        echo json_encode(["success" => false, "error" => "Missing required fields", 'requesterEmail' => $requesterEmail, 'slot_id' => $slot_id]);
        exit;
    }

    try {
        // Connect to the database
        $database = new PDO('sqlite:ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if the email already exists. If it does, get the user_id
        $stmt = $database->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$requesterEmail]);
        $requesterInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($requesterInfo) === 0) {
            $requesterID = "";
        } else {
            $requesterID = $requesterInfo[0]['user_id'];
        }

        // Insert the booking into the database
        $stmt = $database->prepare("INSERT INTO Bookings (slot_id, booked_by_id, booked_by_email) VALUES (?, ?, ?)");
        $stmt->execute([$slot_id, $requesterID, $requesterEmail]);
    
        $response = [
            "success" => true,
        ];
    
        // Send JSON response
        echo json_encode($response);
        exit;
    } catch (Exception $e){
        // Send JSON response
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
        exit;
    }
  
} else {
    // Send JSON response
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
    exit;
}
?>