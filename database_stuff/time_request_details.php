<?php
session_start();
// GRETA ZU
// display all the time requests others have made to me that have status pending 
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
    // Get user details
    $stmt = $database->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->execute([$userID]);
    $userinfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($userinfo) === 0) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    $userEmail = $userinfo[0]['email'];
    $userFname = $userinfo[0]['fname'];
    $userLname = $userinfo[0]['lname'];
    // Get all the TimeRequests that have receiver_id = $userID and status = 'pending'
    $stmt = $database->prepare("SELECT * FROM TimeRequests WHERE receiver_id = ? AND status = 'pending'");
    $stmt->execute([$userID]);
    $pendingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($pendingEvents) === 0) {
        echo json_encode(['success' => false, 'error' => 'No pending events']);
        exit;
    }
    // Get the sender's details using the sender_id
    $stmt = $database->prepare("SELECT * FROM Users WHERE user_id = ?");
    foreach ($pendingEvents as $key => $event) {
        $stmt->execute([$event['sender_id']]);
        $senderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pendingEvents[$key]['senderDetails'] = $senderDetails[0];
    }
    // Get the timerequest details using the request_id
    $stmt = $database->prepare("SELECT * FROM TimeRequests WHERE request_id = ?");
    foreach ($pendingEvents as $key => $event) {
        $stmt->execute([$event['request_id']]);
        $requestDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pendingEvents[$key]['requestDetails'] = $requestDetails[0];
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

$userDetails = [
    "userEmail" => $userEmail,
    "userFname" => $userFname,
    "userLname" => $userLname,
];
foreach ($pendingEvents as $key => $event) {
    $pendingEvents[$key] = [
        "requestID" => $event['request_id'],
        "senderID" => $event['sender_id'],

        "senderName" => $event['senderDetails']['fname'] . ' ' . $event['senderDetails']['lname'],
        "requestName" => $event['requestDetails']['eventName'],
        "requestDateTime" => $event['requestDetails']['start_date'] . ' at ' . $event['requestDetails']['start_time'] . '-' . $event['requestDetails']['end_time'],
        "requestNotes" => $event['requestDetails']['description'],
        "requestStatus" => $event['status'],
        "requestID" => $event['request_id'],
    ];
}

$response = [
    "success" => true,
    "userDetails" => $userDetails,
    "pendingEvents" => $pendingEvents,
];

echo json_encode($response);
exit;
?>