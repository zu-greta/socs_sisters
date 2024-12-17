<?php
//booking.php
session_start();

function validateToken($token) {
    // Check if the token is valid
    // If it is, return the event details
    // If not, return false
    $database = new PDO('sqlite:./database_stuff/ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $database->prepare("SELECT * FROM Tokens WHERE token = ?");
    $stmt->execute([$token]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($event) {
        return [
            'creator_id' => $event['creator_id'],
            'eventName' => $event['eventName'],
            'eventDuration' => $event['eventDuration'],
            'eventLocation' => $event['eventLocation'], 
            'eventCreation' => $event['eventCreation']
        ];
    } else {
        return ['error' => 'Invalid token'];
    }
}

if (isset($_GET['creator_id'])) {
    include './frontend_stuff/booking.html';
    exit;
}

if (isset($_GET['token'])) {    
    $token = $_GET['token'];

    var_dump($_GET['token']); // Debug output

    $data = validateToken($token);
    if (isset($data['error'])) {
        echo json_encode(['success' => false, 'error' => $data['error']]);
        exit;
    }

    $htmlFile = './frontend_stuff/booking.html';

    
    // $creatorId = htmlspecialchars($data['creator_id'], ENT_QUOTES, 'UTF-8');
    // $eventName = htmlspecialchars($data['eventName'], ENT_QUOTES, 'UTF-8');
    // $eventDuration = htmlspecialchars($data['eventDuration'], ENT_QUOTES, 'UTF-8');
    // $eventLocation = htmlspecialchars($data['eventLocation'], ENT_QUOTES, 'UTF-8');
    // $eventCreation = htmlspecialchars($data['eventCreation'], ENT_QUOTES, 'UTF-8');
    //dont encode
    $creatorId = $data['creator_id'];
    $eventName = $data['eventName'];
    $eventDuration = $data['eventDuration'];
    $eventLocation = $data['eventLocation'];
    $eventCreation = $data['eventCreation'];

    //NOT THE MOST SECURE
    $redirectURL = "booking?" . http_build_query([
        'token' => $token,
        'creator_id' => $creatorId,
        'eventName' => $eventName,
        'eventDuration' => $eventDuration,
        'eventLocation' => $eventLocation, 
        'eventCreation' => $eventCreation
    ]);

    //echo $redirectURL;
    header("Location: $redirectURL");

    exit;
} else {
    var_dump($_GET); // Debug output
    echo json_encode(['success' => false, 'error' => 'Token not provided WHY']);
    exit;
}
?>