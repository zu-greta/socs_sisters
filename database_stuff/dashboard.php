<?php
session_start();
// GRETA ZU
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
    // Get user made upcoming events (all)
    $stmt = $database->prepare("SELECT * FROM Events WHERE creator_id = ? AND (start_date > date('now') OR (start_date = date('now') AND start_time >= time('now'))) ORDER BY start_date ASC");
    $stmt->execute([$userID]);
    $upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Get user made past events (history)
    $stmt = $database->prepare("SELECT * FROM Events WHERE creator_id = ? AND (start_date < date('now') OR (start_date = date('now') AND start_time < time('now'))) ORDER BY start_date DESC;");
    $stmt->execute([$userID]);
    $pastEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Get user booked upcoming events (i booked other people's events)
    $stmt = $database->prepare("SELECT * FROM Events WHERE slot_id IN (SELECT slot_id FROM Bookings WHERE booked_by_id = ?) AND start_date >= date('now') ORDER BY start_date ASC");
    $stmt->execute([$userID]);
    $bookedUpcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Get user booked past events
    $stmt = $database->prepare("SELECT * FROM Events WHERE slot_id IN (SELECT slot_id FROM Bookings WHERE booked_by_id = ?) AND start_date < date('now') ORDER BY start_date DESC");
    $stmt->execute([$userID]);
    $bookedPastEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Get upcoming user events that are booked by others
    $stmt = $database->prepare("SELECT * FROM Events WHERE slot_id IN (SELECT slot_id FROM Bookings WHERE slot_id IN (SELECT slot_id FROM Events WHERE creator_id = ?) AND booked_by_id != ?) AND start_date >= date('now') ORDER BY start_date ASC");
    $stmt->execute([$userID, $userID]);
    $bookedEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

$userDetails = [
    "userEmail" => $userEmail,
    "userFname" => $userFname,
    "userLname" => $userLname,
];
//event host is me, events that are booked by others
foreach ($bookedEvents as $key => $event) {
    //get the email of the one who booked the event. might not be an user!
    $stmt = $database->prepare("SELECT booked_by_email FROM Bookings WHERE slot_id = ?");
    $stmt->execute([$event['slot_id']]);
    $bookerEmail = $stmt->fetch(PDO::FETCH_ASSOC);
    $bookerEmail = $bookerEmail['booked_by_email'];

    $bookedEvents[$key] = [
        "eventID" => $event['slot_id'],
        "eventName" => $event['event_name'],
        "eventDate" => "{$event['start_date']}, {$event['start_time']} - {$event['end_date']}, {$event['end_time']}",
        "eventStartDate" => $event['start_date'],
        "eventStartTime" => $event['start_time'],
        "eventEndDate" => $event['end_date'],
        "eventEndTime" => $event['end_time'],
        "eventLocation" => $event['location'],
        "eventNotes" => $event['notes'],
        "eventURL" => $event['url'],
        "eventHost" => $userFname . ' ' . $userLname,   // booked by
        "bookerEmail" => $bookerEmail,
    ];
}
//event host is me. events that are created by me upcoming!
foreach ($upcomingEvents as $key => $event) {
    $upcomingEvents[$key] = [
        "eventID" => $event['slot_id'],
        "eventName" => $event['event_name'],
        "eventDate" => "{$event['start_date']}, {$event['start_time']} - {$event['end_date']}, {$event['end_time']}",
        "eventStartDate" => $event['start_date'],
        "eventStartTime" => $event['start_time'],
        "eventEndDate" => $event['end_date'],
        "eventEndTime" => $event['end_time'],
        "eventLocation" => $event['location'],
        "eventNotes" => $event['notes'],
        "eventURL" => $event['url'],
        "eventHost" => $userFname . ' ' . $userLname,   
    ];
}
foreach ($pastEvents as $key => $event) {
    $pastEvents[$key] = [
        "eventID" => $event['slot_id'],
        "eventName" => $event['event_name'],
        "eventDate" => "{$event['start_date']}, {$event['start_time']} - {$event['end_date']}, {$event['end_time']}",
        "eventStartDate" => $event['start_date'],
        "eventStartTime" => $event['start_time'],
        "eventEndDate" => $event['end_date'],
        "eventEndTime" => $event['end_time'],
        "eventLocation" => $event['location'],
        "eventNotes" => $event['notes'],
        "eventURL" => $event['url'],
        "eventHost" => $userFname . ' ' . $userLname, 
    ];
}
//event host is the username of the event creator, events that are booked by me
$slot_ids = array_column($bookedUpcomingEvents, 'slot_id'); // Get all slot_ids
if (!empty($slot_ids)) {
    $placeholders = implode(',', array_fill(0, count($slot_ids), '?')); 
    $query = "
        SELECT 
            e.slot_id, 
            e.event_name, 
            e.start_date, 
            e.start_time, 
            e.end_date, 
            e.end_time, 
            e.location, 
            e.note AS notes, 
            e.url
        FROM 
            Events e
        JOIN 
            Users u ON e.creator_id = u.user_id
        WHERE 
            e.slot_id IN ($placeholders) 
    ";
    $stmt = $database->prepare($query);
    $stmt->execute($slot_ids);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($bookedUpcomingEvents as $key => $event) {
        $slotId = $event['slot_id'];
        $eventDetails = array_filter($results, fn($res) => $res['slot_id'] == $slotId);
        //get the host information for the event using the creator_id and the User table
        $stmt = $database->prepare("SELECT * FROM Users WHERE user_id = (SELECT creator_id FROM Events WHERE slot_id = ?)");
        $stmt->execute([$slotId]);
        $eventHost = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($eventDetails)) {
            $details = reset($eventDetails); 
            $bookedUpcomingEvents[$key] = [
                "eventID" => $event['slot_id'],
                "eventName" => $details['event_name'],
                "eventDate" => "{$details['start_date']}, {$details['start_time']} - {$details['end_date']}, {$details['end_time']}",
                "eventStartDate" => $details['start_date'],
                "eventStartTime" => $details['start_time'],
                "eventEndDate" => $details['end_date'],
                "eventEndTime" => $details['end_time'],
                "eventLocation" => $details['location'],
                "eventNotes" => $details['notes'],
                "eventURL" => $details['url'],
                "eventHost" => $eventHost['fname'] . ' ' . $eventHost['lname'] . ' (' . $eventHost['email'] . ')',
            ];
        }
    }
}
//event host is the username of the event creator, events that are booked by me
$slot_ids = array_column($bookedPastEvents, 'slot_id'); // Get all slot_ids
if (!empty($slot_ids)) {
    $placeholders = implode(',', array_fill(0, count($slot_ids), '?')); 
    $query = "
        SELECT 
            e.slot_id, 
            e.event_name, 
            e.start_date, 
            e.start_time, 
            e.end_date, 
            e.end_time, 
            e.location, 
            e.note AS notes, 
            e.url
        FROM 
            Events e
        JOIN 
            Users u ON e.creator_id = u.user_id
        WHERE 
            e.slot_id IN ($placeholders) 
    ";
    $stmt = $database->prepare($query);
    $stmt->execute($slot_ids);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($bookedPastEvents as $key => $event) {
        $slotId = $event['slot_id'];
        $eventDetails = array_filter($results, fn($res) => $res['slot_id'] == $slotId);
        //get the host information for the event using the creator_id and the User table
        $stmt = $database->prepare("SELECT * FROM Users WHERE user_id = (SELECT creator_id FROM Events WHERE slot_id = ?)");
        $stmt->execute([$slotId]);
        $eventHost = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($eventDetails)) {
            $details = reset($eventDetails); 
            $bookedPastEvents[$key] = [
                "eventID" => $event['slot_id'],
                "eventName" => $details['event_name'],
                "eventDate" => "{$details['start_date']}, {$details['start_time']} - {$details['end_date']}, {$details['end_time']}",
                "eventStartDate" => $details['start_date'],
                "eventStartTime" => $details['start_time'],
                "eventEndDate" => $details['end_date'],
                "eventEndTime" => $details['end_time'],
                "eventLocation" => $details['location'],
                "eventNotes" => $details['notes'],
                "eventURL" => $details['url'],
                "eventHost" => $eventHost['fname'] . ' ' . $eventHost['lname'] . ' (' . $eventHost['email'] . ')',
            ];
        }
    }
}

$response = [
    "success" => true,
    "userDetails" => $userDetails,
    "upcomingEvents" => $upcomingEvents,
    "pastEvents" => $pastEvents,
    "bookedUpcomingEvents" => $bookedUpcomingEvents,
    "bookedPastEvents" => $bookedPastEvents,
    "bookedEvents" => $bookedEvents,
];

echo json_encode($response);
exit;

?>