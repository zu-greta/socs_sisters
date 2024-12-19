<?php
session_start();
// GRETA ZU
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $poll_id = $_GET['poll_id'] ?? null;
    if (!$poll_id) {
        echo json_encode(['success' => false, 'message' => 'poll_id missing']);
        exit;
    }

try {
    $database = new PDO('sqlite:ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Enable foreign key constraints
    $database->exec('PRAGMA foreign_keys = ON');
    // Check if the event exists
    //TODO :
    $stmt = $database->prepare("SELECT * FROM MeetingPolls WHERE poll_id = ?");
    $stmt->execute([$poll_id]);
    $eventinfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$eventinfo) {
        echo json_encode(['success' => false, 'error' => 'Poll not found']);
        exit;
    }

    // Delete the poll (this will cascade delete related polloptions)
    $deleteEventStmt = $database->prepare("DELETE FROM MeetingPolls WHERE poll_id = ?");
    $deleteEventStmt->execute([$poll_id]);

    echo json_encode(['success' => true, 'message' => 'Poll and related options deleted successfully']);
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
