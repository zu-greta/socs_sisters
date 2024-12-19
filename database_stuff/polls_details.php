<?php
session_start();
// GRETA ZU
//display all polls made by the user
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
        $polls[$key]['options'] = $options;
        foreach ($options as $key2 => $option) {
            $startTime = new DateTime($option['start_time']);
            $endTime = new DateTime($option['end_time']);
            $formattedStartTime = $startTime->format('H:i');
            $formattedEndTime = $endTime->format('H:i');
            // used as the name of the option in the frontend
            $polls[$key]['options'][$key2]['name'] = $option['start_date'] . " (" . $formattedStartTime . " - " . $formattedEndTime . ")";
            //poll_id
            $polls[$key]['options'][$key2]['poll_id'] = $option['poll_id'];
        }
        $polls[$key]['total_votes'] = array_sum(array_column($options, 'vote_count'));
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

$response = [
    'success' => true,
    'polls' => $polls,
];

echo json_encode($response);
exit;
?>