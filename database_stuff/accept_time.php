<?php
session_start();
$userID = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $eventID = $_GET['eventID'] ?? null;
    if (!$eventID) {
        echo json_encode(['success' => false, 'message' => 'Event ID missing']);
        exit;
    }


try {
    $database = new PDO('sqlite:ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Check if the event exists
    $stmt = $database->prepare("SELECT * FROM TimeRequests WHERE request_id = ?");
    $stmt->execute([$eventID]);
    $requestInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    $requestDetails = [
        'requestName' => $requestInfo['eventName'],
        'requestStartTime' => $requestInfo['start_time'],
        'requestEndTime' => $requestInfo['end_time'],
        'requestDate' => $requestInfo['start_date'],
        'sender_id' => $requestInfo['sender_id'],
    ];

    if (!$requestInfo) {
        echo json_encode(['success' => false, 'error' => 'Event not found']);
        exit;
    }

    // change the status of the request to accepted
    $updateRequestStmt = $database->prepare("UPDATE TimeRequests SET status = 'approved' WHERE request_id = ?");
    $updateRequestStmt->execute([$eventID]);

    $response = [
        "success" => true,
        "requestDetails" => $requestDetails,
    ];

    // Send JSON response
    echo json_encode($response);
    exit;

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
}
else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}
?>

