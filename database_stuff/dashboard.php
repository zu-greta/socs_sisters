<?php
session_start();
$userID = $_SESSION['user_id'];
try {
    $database = new PDO('sqlite:ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Get user details
    $stmt = $database->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->execute([$userID]);
    $userinfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($userinfo) === 0) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    $userEmail = $userinfo[0]['email'];
    // $username = $userinfo[0]['username'];
    $userFname = $userinfo[0]['fname'];
    $userLname = $userinfo[0]['lname'];
    // Get user made upcoming events
    $stmt = $database->prepare("SELECT * FROM Events WHERE creator_id = ? AND (start_date > date('now') OR (start_date = date('now') AND start_time >= time('now'))) ORDER BY start_date ASC");
    $stmt->execute([$userID]);
    $upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Get user made past events
    $stmt = $database->prepare("SELECT * FROM Events WHERE creator_id = ? AND (start_date < date('now') OR (start_date = date('now') AND start_time < time('now'))) ORDER BY start_date DESC;");
    $stmt->execute([$userID]);
    $pastEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Get user booked upcoming events
    $stmt = $database->prepare("SELECT * FROM Events WHERE slot_id IN (SELECT slot_id FROM Bookings WHERE booked_by_id = ?) AND start_date >= date('now') ORDER BY start_date ASC");
    $stmt->execute([$userID]);
    $bookedUpcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Get user booked past events
    $stmt = $database->prepare("SELECT * FROM Events WHERE slot_id IN (SELECT slot_id FROM Bookings WHERE booked_by_id = ?) AND start_date < date('now') ORDER BY start_date DESC");
    $stmt->execute([$userID]);
    $bookedPastEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Get upcoming user events using the slot_id in the events table where creator_id=userID, that have been booked by someone else (slot_id -> booking_id are in the bookings table)
    $stmt = $database->prepare("SELECT * FROM Events WHERE slot_id IN (SELECT slot_id FROM Bookings WHERE slot_id IN (SELECT slot_id FROM Events WHERE creator_id = ?) AND booked_by_id != ?) AND start_date >= date('now') ORDER BY start_date ASC");
    $stmt->execute([$userID, $userID]);
    $bookedEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

//events display: event name, event start date, event start time, event end date, event end time, event location, event notes, event url
// prepare user details + user made upcoming events + user made past events + user booked upcoming events + user booked past events
$userDetails = [
    // "username" => $username,
    "userEmail" => $userEmail,
    "userFname" => $userFname,
    "userLname" => $userLname,
];
//event host is me, events that are booked by others
foreach ($bookedEvents as $key => $event) {
    $bookedEvents[$key] = [
        //give the event id too
        "eventID" => $event['slot_id'],
        "eventName" => $event['event_name'],
        //concatenate date and time format: start date, start time - end date, end time
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
//event host is me
foreach ($upcomingEvents as $key => $event) {
    $upcomingEvents[$key] = [
        //give the event id too
        "eventID" => $event['slot_id'],
        "eventName" => $event['event_name'],
        //concatenate date and time format: start date, start time - end date, end time
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
        //give the event id too
        "eventID" => $event['slot_id'],
        "eventName" => $event['event_name'],
        //concatenate date and time format: start date, start time - end date, end time
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
//event host is the username of the event creator
$slot_ids = array_column($bookedUpcomingEvents, 'slot_id'); // Get all slot_ids
if (!empty($slot_ids)) {
    $placeholders = implode(',', array_fill(0, count($slot_ids), '?')); // Create placeholders for the query based on the number of slot_ids
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

        if (!empty($eventDetails)) {
            $details = reset($eventDetails); // Get the matching result
            $bookedUpcomingEvents[$key] = [
                //give the event id too
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
                "eventHost" => $details['eventHost'],
            ];
        }
    }
}
$slot_ids = array_column($bookedPastEvents, 'slot_id'); // Get all slot_ids
if (!empty($slot_ids)) {
    $placeholders = implode(',', array_fill(0, count($slot_ids), '?')); // Create placeholders for the query based on the number of slot_ids
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

        if (!empty($eventDetails)) {
            $details = reset($eventDetails); // Get the matching result
            $bookedPastEvents[$key] = [
                //give the event id too
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
                "eventHost" => $details['eventHost'],
            ];
        }
    }
}

// Print out everything as json to debug
// echo ('user: ');
// echo json_encode($userDetails);
// echo ('\n');
// echo ('upcoming: ');
// echo json_encode($upcomingEvents);
// echo ('\n');
// echo ('past: ');
// echo json_encode($pastEvents);
// echo ('\n');
//  echo ('booked upcoming: ');
// echo json_encode($bookedUpcomingEvents);
// echo ('\n');
// echo ('booked past: ');
// echo json_encode($bookedPastEvents);


$response = [
    "success" => true,
    "userDetails" => $userDetails,
    "upcomingEvents" => $upcomingEvents,
    "pastEvents" => $pastEvents,
    "bookedUpcomingEvents" => $bookedUpcomingEvents,
    "bookedPastEvents" => $bookedPastEvents,
    "bookedEvents" => $bookedEvents,
];

// Send JSON response
echo json_encode($response);
exit;

?>