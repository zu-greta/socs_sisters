<?php
// Start a session
session_start();

// Function to validate the token
function validateToken($token) {
    // Connect to the database
    $database = new PDO('sqlite:./database_stuff/ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query the Tokens table
    $stmt = $database->prepare("SELECT * FROM Tokens WHERE token = ?");
    $stmt->execute([$token]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return event details if the token is valid
    if ($event) {
        return [
            'creator_id' => $event['creator_id'],
            'eventName' => $event['eventName'],
            'eventDuration' => $event['eventDuration'],
            'eventLocation' => $event['eventLocation']
        ];
    } else {
        return ['error' => 'Invalid token'];
    }
}

if (isset($_GET['creator_id'])) {
    include './frontend_stuff/polling.html';
    exit;
}


// Check if the script is being accessed with the token
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if this is already redirected
    if (!isset($_GET['redirected'])) {
        // Validate the token
        $eventData = validateToken($token);
        if (isset($eventData['error'])) {
            echo json_encode(['success' => false, 'error' => $eventData['error']]);
            exit;
        }

        // Connect to the database to fetch poll details
        $database = new PDO('sqlite:./database_stuff/ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch poll details from MeetingPolls
        $stmt = $database->prepare("SELECT * FROM MeetingPolls WHERE creator_id = ? AND event_name = ?");
        $stmt->execute([$eventData['creator_id'], $eventData['eventName']]);
        $poll = $stmt->fetch(PDO::FETCH_ASSOC);

        $htmlFile = './frontend_stuff/polling.html';

        if ($poll) {
            // Prepare data for redirection
            $creatorId = htmlspecialchars($eventData['creator_id'], ENT_QUOTES, 'UTF-8');
            $eventName = htmlspecialchars($eventData['eventName'], ENT_QUOTES, 'UTF-8');
            $eventDuration = htmlspecialchars($eventData['eventDuration'], ENT_QUOTES, 'UTF-8');
            $eventLocation = htmlspecialchars($eventData['eventLocation'], ENT_QUOTES, 'UTF-8');
            $pollId = htmlspecialchars($poll['poll_id'], ENT_QUOTES, 'UTF-8');

            // Construct the redirect URL with poll and event details
            $redirectURL = "polling?" . http_build_query([
                'token' => $token,
                'creator_id' => $creatorId,
                'eventName' => $eventName,
                'eventDuration' => $eventDuration,
                'eventLocation' => $eventLocation,
                'poll_id' => $pollId,
                'redirected' => 'true' // Add flag to indicate redirection
            ]);

            // Redirect to the polling page with arguments
            header("Location: $redirectURL");
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => 'Poll not found']);
            exit;
        }
    } else {
        // Already redirected, proceed with the page logic
        echo json_encode(['success' => true, 'message' => 'You are redirected successfully.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Token not provided']);
    exit;
}
