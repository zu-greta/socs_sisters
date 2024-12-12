<?php
session_start();
$creatorId = $_SESSION['user_id'];
try {
    $database = new PDO('sqlite:ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $database->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->execute([$creatorId]);
    $userinfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($userinfo) === 0) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }
    $creatorEmail = $userinfo[0]['email'];
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
// validate everything and then insert into the database
// first calculate the different slots for the event. if one-time simply (enddate-startdate)/duration for the number of slots and then 
// create the slots going with startdate + i*duration for i=0 to number of slots. 
// if recurring, then check the start and end dates, if recurring every monday from startdate to enddate then find all mondays 
// in that range and then create slots for each monday.
// for all slots created, insert into the database


// Handle POST request to schedule recurring or one-time events
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate the form data
    $name = $_POST['name'] ?? '';
    $location = $_POST['location'] ?? '';
    $start = $_POST['start_time'] ?? '';
    $end = $_POST['end_time'] ?? '';
    $participants = $_POST['participants'] ?? '';
    $slotDuration = $_POST['slot'] ?? '';
    // $calendar = $_POST['calendar'] ?? '';
    $notes = $_POST['notes'] ?? '';
    

    //TODO: CHECK Y TIME CANNOT BE VALIDATED
    if (empty($name) || empty($location) || empty($participants)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields for one-time events', 'slotDuration' => $slotDuration]); 
        //echo json_encode(['name' => $name, 'location' => $location, 'startTime' => $startTime, 'endTime' => $endTime, 'slotDuration' => $slotDuration, 'participants' => $participants, 'calendar' => $calendar]);   
        exit;
    }

    // split up the time and date
    $startTime = explode('T', $start)[1];
    $startDate = explode('T', $start)[0];
    $endTime = explode('T', $end)[1];
    $endDate = explode('T', $end)[0];

    $startDateObj = new DateTime($start);
    $endDateObj = new DateTime($end);
    $startTimeObj = new DateTime($startTime);
    $endTimeObj = new DateTime($endTime);

    if (strtotime($start) > strtotime($end)) {
        echo json_encode(['success' => false, 'error' => 'Start date must be before end date']);
        exit;
    }

    // TODO: For one-time events, calculate the slots (duration-based) 
    $slots = [];
    $currentDate = clone $startDateObj;
    $duration = new DateInterval('PT' . $slotDuration . 'M');
    if ($slotDuration < 1) {
        $numSlots = 1;
        // slotDuration = 0 means the slot is the entire event. the duration should be the entire event time
        if ($startDate === $endDate) {
            $timediff = ($endTimeObj->getTimestamp() - $startTimeObj->getTimestamp())/60;
            $duration = new DateInterval('PT' . $timediff . 'M');
            $slotDuration = $timediff;
        } else {
            $duration = new DateInterval('PT' . $startDateObj->diff($endDateObj)->days*24*60 . 'M');
            $slotDuration = $startDateObj->diff($endDateObj)->days * 24*60;
        }
    } else{
        //if the start and end date are the same, the number of slots is the difference in minutes divided by the slot duration
        //otherwise, the number of slots is the difference in days times 24 hours times 60 minutes divided by the slot duration
        if ($startDate === $endDate) {
            $timediff = ($endTimeObj->getTimestamp() - $startTimeObj->getTimestamp())/60;
            $numSlots = $timediff / $slotDuration;
        } else {
            $numSlots = $startDateObj->diff($endDateObj)->days * 24 * 60 / $slotDuration;
        }
    }
    $link = "http://cs.mcgill.ca/~gzu/socs_sisters/booking/event?creator_id=" . urlencode($creatorId) . "&eventName=" . urlencode($name) . "&eventDuration=" . urlencode($slotDuration) . "&eventLocation=" . urlencode($location); // TODO replace with actual link
    for ($i = 0; $i < $numSlots; $i++) {
        $slots[] = [
            'start_date' => $currentDate->format('Y-m-d'),
            'start_time' => $currentDate->format('H:i:s'),
            'end_date' => $currentDate->format('Y-m-d'),
            'end_time' => $currentDate->add($duration)->format('H:i:s'),
            'duration' => $slotDuration,
            'event_name' => $name,
            'note' => $notes,
            'location' => $location,
            'max_people' => $participants,
            'creator_id' => $creatorId, 
            'url' => $link,
        ];
    }


    // Insert each slot into the database
    try {
        $database = new PDO('sqlite:ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        
        // Prepare the insert statement
        $stmt = $database->prepare(
            "INSERT INTO Events (creator_id, start_date, end_date, duration, start_time, end_time, event_name, note, location, max_people, url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        //Insert each slot
        foreach ($slots as $slot) {
            $stmt->execute([
                $slot['creator_id'], 
                $slot['start_date'], 
                $slot['end_date'], // Same date for start and end TODO when past midnight its the next day!
                $slot['duration'], 
                $slot['start_time'], 
                $slot['end_time'], 
                $slot['event_name'], 
                $slot['note'], 
                $slot['location'], 
                $slot['max_people'],
                $slot['url']   
            ]);
        }

        // the pop up message modal should be displayed now over the schedule page
        $eventDetails = [
            "name" => $name,
            "location" => $location,
            "start_time" => $startTime,
            "end_time" => $endTime,
            "start_date" => $startDate,
            "end_date" => $endDate,
            "participants" => $participants,
            "slotDuration" => $slotDuration,
            "calendar" => "calendar REMOVE THIS FROM THE FRONTEND HTML",
            "notes" => $notes,
            "creator_id" => $creatorId, 
            "email" => $creatorEmail, 
            "link" => $link, 
        ];

        $response = [
            "success" => true,
            "eventDetails" => $eventDetails,
        ];

        // Send JSON response
        echo json_encode($response);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
