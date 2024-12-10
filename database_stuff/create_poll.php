<?php
header('Content-Type: application/json');



try {
    $database = new PDO('sqlite:ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Input data from the frontend
$data = json_decode(file_get_contents('php://input'), true);
$pollTitle = $data['pollTitle'] ?? null;
$availableSlots = $data['availableSlots'] ?? null;

if (!$pollTitle || !$availableSlots || !is_array($availableSlots)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

// Hardcoded creator_id for now
$creatorId = 1;

try {
    // Start a transaction
    $database->beginTransaction();

    // Insert into MeetingPolls table
    $pollURL = 'https://example.com/poll/' . uniqid();
    $stmt = $database->prepare("INSERT INTO MeetingPolls (creator_id, event_name, url) VALUES (:creator_id, :event_name, :url)");
    $stmt->execute([
        ':creator_id' => $creatorId,
        ':event_name' => $pollTitle,
        ':url' => $pollURL
    ]);

    // Get the last inserted poll_id
    $pollId = $database->lastInsertId();

    // Insert into PollOptions table
    $stmt = $database->prepare("INSERT INTO PollOptions (poll_id, start_date, end_date, duration, start_time, end_time) 
                           VALUES (:poll_id, :start_date, :end_date, :duration, :start_time, :end_time)");

    foreach ($availableSlots as $slot) {
        $stmt->execute([
            ':poll_id' => $pollId,
            ':start_date' => $slot['start_date'],
            ':end_date' => $slot['end_date'],
            ':duration' => $slot['duration'],
            ':start_time' => $slot['start_time'],
            ':end_time' => $slot['end_time']
        ]);
    }

    // Commit the transaction
    $database->commit();

    echo json_encode(['status' => 'success', 'message' => 'Poll created successfully', 'poll_url' => $pollURL]);
} catch (Exception $e) {
    $database->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Failed to create poll: ' . $e->getMessage()]);
}
?>
