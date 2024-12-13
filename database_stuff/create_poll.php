<?php
session_start();

header('Content-Type: application/json');

try {
    // Check user authentication
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated.');
    }
    $creatorId = $_SESSION['user_id'];

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate input
    $pollTitle = $input['title'] ?? '';
    $pollURL = $input['url'] ?? '';
    $slots = $input['slots'] ?? [];
    $token = parse_url($pollURL, PHP_URL_QUERY);
    $token = str_replace('token=', '', $token);


    if (empty($pollTitle) || empty($pollURL) || empty($slots) || empty($token)) {
        throw new Exception('Invalid input: title, URL, slots, and token are required.');
    }

    // Database connection
    $database = new PDO('sqlite:ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Start transaction
    $database->beginTransaction();

    // Insert token into Tokens table
    $stmt = $database->prepare("
        INSERT INTO Tokens (token, creator_id, eventName, eventDuration, eventLocation)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$token, $creatorId, $pollTitle, -1, 'TBD']);

    // Insert poll into MeetingPolls
    $stmt = $database->prepare("INSERT INTO MeetingPolls (creator_id, event_name, url) VALUES (?, ?, ?)");
    $stmt->execute([$creatorId, $pollTitle, $pollURL]);
    $pollId = $database->lastInsertId();

    // Insert slots into PollOptions
    $stmt = $database->prepare("
        INSERT INTO PollOptions (poll_id, start_date, end_date, duration, start_time, end_time)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($slots as $slot) {
        if (preg_match('/^(\d{4}-\d{2}-\d{2}): (\d{1,2}:\d{2} [APM]{2}) - (\d{1,2}:\d{2} [APM]{2})$/', $slot, $matches)) {
            $startDate = $matches[1];
            $startTime = date("H:i:s", strtotime($matches[2]));
            $endTime = date("H:i:s", strtotime($matches[3]));
            $endDate = $startDate; // Assuming single-day slots
            $duration = (strtotime($endTime) - strtotime($startTime)) / 60;

            $stmt->execute([$pollId, $startDate, $endDate, $duration, $startTime, $endTime]);
        } else {
            throw new Exception('Invalid slot format: ' . $slot);
        }
    }

    // Commit transaction
    $database->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($database) && $database->inTransaction()) {
        $database->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
