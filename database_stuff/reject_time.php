<?php
session_start();

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

    if (!$requestInfo) {
        echo json_encode(['success' => false, 'error' => 'Event not found']);
        exit;
    }

    // Change the status of the request to rejected
    $updateRequestStmt = $database->prepare("UPDATE TimeRequests SET status = 'declined' WHERE request_id = ?");
    $updateRequestStmt->execute([$eventID]);

    echo json_encode(['success' => true, 'message' => 'Rejected successfully']);
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

