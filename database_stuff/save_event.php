<?php
session_start();
$creatorId = $_SESSION['user_id'];
try {
    $database = new PDO('sqlite:ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $database->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->execute([$creatorId]);
    $userinfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($userinfo) === 0) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    $creatorEmail = $userinfo[0]['email'];
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventName = $_POST['eventName'] ?? '';
    $eventLocation = $_POST['eventLocation'] ?? '';
    $eventStartTime = $_POST['eventStartTime'] ?? '';
    $eventEndTime = $_POST['eventEndTime'] ?? '';
    $eventDate = $_POST['eventDate'] ?? '';
    $eventRequestID = $_POST['eventRequestID'] ?? '';
    $eventSenderID = $_POST['sender_id'] ?? '';    

    // Validate the form data
    if (empty($eventName) || empty($eventLocation) || empty($eventStartTime) || empty($eventEndTime) || empty($eventDate) || empty($eventRequestID) || empty($eventSenderID)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields.', 'eventName' => $eventName, 'eventLocation' => $eventLocation, 'eventStartTime' => $eventStartTime, 'eventEndTime' => $eventEndTime, 'eventDate' => $eventDate, 'eventRequestID' => $eventRequestID, 'eventSenderID' => $eventSenderID]);
        exit;
    }

    try {
        $database = new PDO('sqlite:ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Prepare the insert statement
        $stmt = $database->prepare(
            "INSERT INTO Events (creator_id, start_date, end_date, duration, start_time, end_time, event_name, note, location, max_people, url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        // creator_id = creator_id, start_date = end_date = eventDate, duration = eventEndTime - eventStartTime, start_time = eventStartTime, end_time = eventEndTime, event_name = eventName, note = '', location = eventLocation, max_people = 1, url = 'REQUESTED'
        $stmt->execute([$creatorId, $eventDate, $eventDate, $eventEndTime - $eventStartTime, $eventStartTime, $eventEndTime, $eventName, '', $eventLocation, 1, 'REQUESTED']);
        
        //get the slot id of the event
        $stmt = $database->prepare("SELECT slot_id FROM Events WHERE creator_id = ? AND start_date = ? AND end_date = ? AND start_time = ? AND end_time = ? AND event_name = ? AND location = ? AND url = 'REQUESTED'");
        $stmt->execute([$creatorId, $eventDate, $eventDate, $eventStartTime, $eventEndTime, $eventName, $eventLocation]);
        $slot_id = $stmt->fetch(PDO::FETCH_ASSOC);
        $slot_id = $slot_id['slot_id'];
        //get the sender email
        $stmt = $database->prepare("SELECT email FROM Users WHERE user_id = ?");
        $stmt->execute([$eventSenderID]);
        $sender_email = $stmt->fetch(PDO::FETCH_ASSOC);
        $sender_email = $sender_email['email'];
        // Prepare the insert statement
        $stmt  = $database->prepare(
            "INSERT INTO Bookings (slot_id, booked_by_id, booked_by_email, num_people)
            VALUES (?, ?, ?, ?)"
        );
        // slot_id = slot_id, booked_by_id = eventSenderID, booked_by_email = eventSenderID, num_people = 1
        $stmt->execute([$slot_id, $eventSenderID, $sender_email, 1]);

        echo json_encode(['success' => true, 'slot_id' => $slot_id, 'eventSenderID' => $eventSenderID, 'sender_email' => $sender_email]);
        //header('Location: ../frontend/temp_timeaccept.html');
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
