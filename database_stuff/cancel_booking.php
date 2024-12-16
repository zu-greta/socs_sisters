<?php
session_start();
// $userID = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $eventID = $_GET['eventID'] ?? null;
    if (!$eventID) {
        echo json_encode(['success' => false, 'message' => 'Event ID missing']);
        exit;
    }


try {
    $database = new PDO('sqlite:ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Enable foreign key constraints
    $database->exec('PRAGMA foreign_keys = ON');
    // Check if the event exists
    $stmt = $database->prepare("SELECT * FROM Events WHERE slot_id = ?");
    $stmt->execute([$eventID]);
    $eventinfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$eventinfo) {
        echo json_encode(['success' => false, 'error' => 'Event not found']);
        exit;
    }

    // Delete the event (this will cascade delete related bookings)
    $deleteEventStmt = $database->prepare("DELETE FROM Events WHERE slot_id = ?");
    $deleteEventStmt->execute([$eventID]);

    echo json_encode(['success' => true, 'message' => 'Event and related bookings deleted successfully']);
    // Disable foreign key constraints
    $database->exec('PRAGMA foreign_keys = OFF');
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
