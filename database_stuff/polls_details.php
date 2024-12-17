<?php
session_start();
// TODO: display all the polls that the user has created and the polls results

//TEMPORARY
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
    $userID = $session['user_id'];
    // Get all the polls the user has made in MeetingPolls with creator_id = $userID
    $stmt = $database->prepare("SELECT * FROM MeetingPolls WHERE creator_id = ?");
    $stmt->execute([$userID]);
    $polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Get the name of the polls (event_name)
    foreach ($polls as $key => $poll) {
        $stmt = $database->prepare("SELECT event_name FROM MeetingPolls WHERE poll_id = ?");
        $stmt->execute([$poll['poll_id']]);
        $polls[$key]['event_name'] = $stmt->fetch(PDO::FETCH_ASSOC)['event_name'];
    }
    if (count($polls) === 0) {
        echo json_encode(['success' => false, 'error' => 'No polls found']);
        exit;
    }
    // Get the options for each poll and the number of votes for each option
    foreach ($polls as $key => $poll) {
        $stmt = $database->prepare("SELECT * FROM PollOptions WHERE poll_id = ?");
        $stmt->execute([$poll['poll_id']]);
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // for each option in the PollOptions table, get the number of votes (vote_count) and the name is the start_date . " : " . $start_time . " - " . $end_time
        $polls[$key]['options'] = $options;
        foreach ($options as $key2 => $option) {
            // start_time and end_time in the format HH:MM
            $startTime = new DateTime($option['start_time']);
            $endTime = new DateTime($option['end_time']);
            $formattedStartTime = $startTime->format('H:i');
            $formattedEndTime = $endTime->format('H:i');
            $polls[$key]['options'][$key2]['name'] = $option['start_date'] . " (" . $formattedStartTime . " - " . $formattedEndTime . ")";
        }
        $polls[$key]['total_votes'] = array_sum(array_column($options, 'vote_count'));
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// $response
$response = [
    'success' => true,
    'polls' => $polls,
];

// Send JSON response
echo json_encode($response);
exit;
?>