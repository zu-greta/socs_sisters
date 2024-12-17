<?php
session_start();
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
    $requesterID = $session['user_id'];
    $stmt = $database->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->execute([$requesterID]);
    $requesterInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($requesterInfo) === 0) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    $requesterEmail = $requesterInfo[0]['email'];
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $creator_id = $_POST['creator_id'] ?? '';
    $eventName = $_POST['eventName'] ?? '';
    $date = $_POST['date'] ?? '';
    $start_time = $_POST['start-time'] ?? '';
    $end_time = $_POST['end-time'] ?? '';
    $reason = $_POST['reason'] ?? '';

    if (empty($creator_id) || empty($eventName) || empty($date) || empty($start_time) || empty($end_time) || empty($reason)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields for one-time events', 'creator_id' => $creator_id, 'date' => $date, 'start_time' => $start_time, 'end_time' => $end_time, 'reason' => $reason]);
        exit;
    }

    if (strtotime($start_time) >= strtotime($end_time)) {
        echo json_encode(['success' => false, 'error' => 'Start time must be before end time']);
        exit;
    }

    $duration = (strtotime($end_time) - strtotime($start_time)) / 60;

    try {
        $database = new PDO('sqlite:ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $database->prepare("SELECT * FROM Users WHERE user_id = ?");
        $stmt->execute([$creator_id]);
        $creatorInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($creatorInfo) === 0) {
            echo json_encode(['success' => false, 'error' => 'Creator not found']);
            exit;
        }
        $creator_email = $creatorInfo[0]['email'];
        //add the timerequest to the database with the requester's id and the creator's id
        $stmt = $database->prepare("INSERT INTO TimeRequests (sender_id, receiver_id, start_date, end_date, duration, start_time, end_time, description, eventName) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$requesterID, $creator_id, $date, $date, $duration, $start_time, $end_time, $reason, $eventName]);
        
        $eventDetails = [
            "date" => $date,
            "start_time" => $start_time,
            "end_time" => $end_time,
            "reason" => $reason,
            "creator_email" => $creator_email,
            "requesterEmail" => $requesterEmail,
            "eventName" => $eventName,
        ];

        $response = [
            "success" => true,
            "eventDetails" => $eventDetails,
        ];

        echo json_encode($response);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>