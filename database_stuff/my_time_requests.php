<?php
session_start();
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
    // Get all the TimeRequests that have receiver_id = $userID 
    $stmt = $database->prepare("SELECT * FROM TimeRequests WHERE sender_id = ?");
    $stmt->execute([$userID]);
    $Requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($Requests) === 0) {
        echo json_encode(['success' => false, 'error' => 'No pending events']);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

$requestDetails = []; 
foreach ($Requests as $key => $event) {
    $stmt = $database->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->execute([$event['receiver_id']]);
    $receiverDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $Requests[$key]['receiverDetails'] = $receiverDetails[0];

    $requestDetails[] = [
        "requestName" => $event['eventName'],
        "requestDateTime" => $event['start_date'] . ' at ' . $event['start_time'] . '-' . $event['end_time'],
        "receiverName" => $receiverDetails[0]['fname'] . ' ' . $receiverDetails[0]['lname'],
        "requestNotes" => $event['description'],
        "requestStatus" => $event['status'],
    ];
}


$response = [
    "success" => true,
    "requestDetails" => $requestDetails,
];

echo json_encode($response);
exit;
?>