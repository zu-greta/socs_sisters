<?php
try {
    $database = new PDO('sqlite:ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Get the creator's info using the creator_id in the URL params)
    $creatorID = $_GET['creator_id'];
    $eventName = $_GET['eventName'];
    $eventDuration = $_GET['eventDuration'];
    $eventLocation = $_GET['eventLocation'];
    $eventCreation = $_GET['eventCreation'];
    $stmt = $database->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->execute([$creatorID]);
    $creatorinfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($creatorinfo) === 0) {
        echo json_encode(['success' => false, 'error' => 'Creator not found']);
        exit;
    }
    $creatorEmail = $creatorinfo[0]['email'];
    $creatorFname = $creatorinfo[0]['fname'];
    $creatorLname = $creatorinfo[0]['lname'];
    // Get all the time slots by the creator for now
    $stmt = $database->prepare("SELECT * FROM Events WHERE creator_id = ? AND event_name = ? AND created_at = ? AND location = ?");
    $stmt->execute([$creatorID, $eventName, $eventCreation, $eventLocation]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

$creator_info = [
    "creatorEmail" => $creatorEmail,
    "creator_name" => $creatorFname . " " . $creatorLname,
];
// Get the time slots 
$time_slots = [];
foreach ($events as $event) {
    //get the number of people booked for this event. check the bookings table for all the bookings with this slot_id.
    // if the number of people booked is equal to the max_people, then the type is full. 
    $stmt = $database->prepare("SELECT COUNT(*) FROM Bookings WHERE slot_id = ?");
    $stmt->execute([$event['slot_id']]);
    $totalBooked = $stmt->fetchColumn();

    $type = 'available';
    if ($totalBooked >= $event['max_people']) {
        $type = 'full';
    } 
    $dayOfWeek = date('l', strtotime($event['start_date'])); // Get day name
    $time_slots[] = [
        "event_id" => $event['slot_id'],
        "start_time" => $event['start_time'],
        "end_time" => $event['end_time'],
        "date" => $event['start_date'],
        "type" => $type, 
        "day" => $dayOfWeek, 
        "location" => $event['location'],
        "description" => $event['note'],
        "host" => $creatorFname . " " . $creatorLname,
    ];
}

$response = [
    "success" => true,
    "creator_info" => $creator_info,
    "time_slots" => $time_slots, 

    "eventName" => $eventName,
    "eventDuration" => $eventDuration,
    "eventCreation" => $eventCreation,
    "eventLocation" => $eventLocation,
    "date" => $event['start_date'],
    "day" => $dayOfWeek,
];

echo json_encode($response);
exit;

?>