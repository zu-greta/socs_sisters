<?php
session_start();
// $creatorId = $_SESSION['user_id'];
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
    $creatorId = $session['user_id'];
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
    
    //creationTime should be a TIMESTAMP
    $creationTime = date('Y-m-d H:i:s');

    if (empty($name) || empty($location) || empty($participants)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields for one-time events', 'slotDuration' => $slotDuration]); 
        //echo json_encode(['name' => $name, 'location' => $location, 'startTime' => $startTime, 'endTime' => $endTime, 'slotDuration' => $slotDuration, 'participants' => $participants, 'calendar' => $calendar]);   
        exit;
    }

    function generateToken($creatorId, $name, $location, $slotDuration, $creationTime) {
        $token = bin2hex(random_bytes(32));
        //save into database
        try {
            $database = new PDO('sqlite:ssDB.sq3');
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $database->prepare("INSERT INTO Tokens (token, creator_id, eventName, eventDuration, eventLocation, eventCreation) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$token, $creatorId, $name, $slotDuration, $location, $creationTime]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
        return $token;
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
    $extraSlot = 0;
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
        //TODO: the last slot should end at the end time. if the end time is before the end of the slot, the slot should be shorter
        if ($startDate === $endDate) {
            $timediff = ($endTimeObj->getTimestamp() - $startTimeObj->getTimestamp())/60;
            $numSlots = $timediff / $slotDuration;
            if ($timediff % $slotDuration != 0) {
                $extraSlot = $timediff % $slotDuration; // the extra time that is not a full slot
            }
        } else {
            $numSlots = $startDateObj->diff($endDateObj)->days * 24 * 60 / $slotDuration;
            if ($startDateObj->diff($endDateObj)->days * 24 * 60 % $slotDuration != 0) {
                $extraSlot = $startDateObj->diff($endDateObj)->days * 24 * 60 % $slotDuration; // the extra time that is not a full slot
            }
        }
    }
    $tokengen = generateToken($creatorId, $name, $location, $slotDuration, $creationTime);
    $link = "http://cs.mcgill.ca/~gzu/socs_sisters/booking?token=" . urlencode($tokengen); 
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
    // add the extra slot if it exists
    if ($extraSlot != 0) {
        $slots[] = [
            'start_date' => $currentDate->format('Y-m-d'),
            'start_time' => $currentDate->format('H:i:s'),
            'end_date' => $currentDate->format('Y-m-d'),
            'end_time' => $currentDate->add(new DateInterval('PT' . $extraSlot . 'M'))->format('H:i:s'),
            'duration' => $extraSlot,
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
            "INSERT INTO Events (creator_id, start_date, end_date, duration, start_time, end_time, event_name, note, location, max_people, url, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
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
                $slot['url'],
                $creationTime
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
            "notes" => $notes,
            "creator_id" => $creatorId, 
            "email" => $creatorEmail, 
            "link" => $link, 

            "creationTime" => $creationTime,
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
