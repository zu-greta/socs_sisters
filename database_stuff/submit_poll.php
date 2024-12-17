<?php
header('Content-Type: application/json');
session_start();

try {
    $db = new PDO('sqlite:ssDB.sq3'); 
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Handle poll submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['poll_id'], $input['option_id'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid input: poll_id or option_id missing.']);
        exit;
    }

    $pollId = $input['poll_id'];
    $optionId = $input['option_id'];

    try {
        // Check if the poll_id and option_id exist in the database
        $stmtCheck = $db->prepare("SELECT * FROM PollOptions WHERE poll_id = ? AND option_id = ?");
        $stmtCheck->execute([$pollId, $optionId]);
        $option = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$option) {
            echo json_encode(['success' => false, 'error' => 'Invalid poll_id or option_id.']);
            exit;
        }

        // Update the vote count for the selected option
        $stmtUpdate = $db->prepare("UPDATE PollOptions SET vote_count = vote_count + 1 WHERE poll_id = ? AND option_id = ?");
        $stmtUpdate->execute([$pollId, $optionId]);

        // Fetch updated options to return as a response
        $stmtOptions = $db->prepare("SELECT * FROM PollOptions WHERE poll_id = ?");
        $stmtOptions->execute([$pollId]);
        $updatedOptions = $stmtOptions->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'message' => 'Vote recorded successfully.', 'options' => $updatedOptions]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Only POST is allowed.']);
}
?>
