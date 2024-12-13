<?php
header('Content-Type: application/json');

// Hardcoded poll data
$pollData = [
    "poll_id" => 1,
    "name" => "Team Meeting Poll",
    "options" => [
        ["id" => 1, "text" => "Option 1: Dec 15, 10:00-12:00", "votes" => 0],
        ["id" => 2, "text" => "Option 2: Dec 16, 11:00-13:00", "votes" => 0],
        ["id" => 3, "text" => "Option 3: Dec 17, 09:00-11:00", "votes" => 0],
        ["id" => 4, "text" => "Option 4: Dec 18, 14:00-16:00", "votes" => 0],
    ],
];

// Handle voting
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $optionId = $input['option_id'];

    // Update the vote count in the hardcoded data
    foreach ($pollData['options'] as &$option) {
        if ($option['id'] === $optionId) {
            $option['votes'] += 1;
        }
    }

    // Return updated poll data
    echo json_encode($pollData);
    exit;
}

// Serve poll data
echo json_encode($pollData);
