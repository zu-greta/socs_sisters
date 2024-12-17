<?php
session_start();
// GRETA ZU
try {
    $database = new PDO('sqlite:ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Get userID from session token (cookies)
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

// Handle POST request to schedule recurring events
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate the form data
    $name = $_POST['name'] ?? '';
    $location = $_POST['location'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $participants = $_POST['participants'] ?? '';
    $slotDuration = $_POST['slot'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // For recurring events, also capture additional details
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $days = isset($_POST['day']) ? $_POST['day'] : [];

    $creationTime = date('Y-m-d H:i:s');
    
    // Validate required fields
    if (empty($name) || empty($location) || empty($startDate) || empty($endDate) || empty($participants) || empty($days)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields for recurring events']);
        exit;
    }

    // tokens table
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

    // Convert dates and times to DateTime objects
    $startDateObj = new DateTime($startDate);
    $endDateObj = new DateTime($endDate);
    $startTimeObj = new DateTime($startTime);
    $endTimeObj = new DateTime($endTime);

    $startDateObj = new DateTime($startDate . ' ' . $startTime); // Combine date and time
    $endDateObj = new DateTime($endDate . ' ' . $endTime); // Combine date and time
    if ($startDate > $endDate) {
        echo json_encode(['success' => false, 'error' => 'Start date must be before end date']);
        exit;
    } else if ($startDate === $endDate && strtotime($startTime) >= strtotime($endTime)) {
        echo json_encode(['success' => false, 'error' => 'Start time must be before end time', 'start' => $startTime, 'end' => $endTime]);
        exit;
    }
    

    $slots = [];
    $currentDate = clone $startDateObj;
    $extraSlot = 0; //TODO
    if ($slotDuration < 1) {
        $timediff = ($endTimeObj->getTimestamp() - $startTimeObj->getTimestamp())/60; 
        $duration = new DateInterval('PT' . $timediff . 'M');
        $slotDuration = $timediff;
    } else {
        $duration = new DateInterval('PT' . $slotDuration . 'M');
    }
    // Loop through each day in the range
    while ($currentDate <= $endDateObj) {
        // Get the current day of the week (e.g., Monday, Tuesday)
        $dayOfWeek = $currentDate->format('l');
        // Check if the current day matches one of the selected days
        if (in_array($dayOfWeek, $days)) {
            $currentDate->setTime($startTimeObj->format('H'), $startTimeObj->format('i'));
            // Calculate the number of slots for the current day
            $dayStart = clone $startTimeObj;
            $dayEnd = clone $endTimeObj; 
            if ($slotDuration < 1) {
                $numSlots = 1;
            } else {
                //if the time diff is less than the slot duration, then the number of slots should be 1 and the slotDuration should be the time diff
                if ($dayStart->diff($dayEnd)->h * 60 + $dayStart->diff($dayEnd)->i < $slotDuration) {
                    $numSlots = 1;
                    $slotDuration = $dayStart->diff($dayEnd)->h * 60 + $dayStart->diff($dayEnd)->i;
                    $duration = new DateInterval('PT' . $slotDuration . 'M');
                } else {
                    $numSlots = ($dayStart->diff($dayEnd)->h * 60 + $dayStart->diff($dayEnd)->i) / $slotDuration;
                    //the last slot should end at the end time. if the end time is before the end of the slot, the slot should be shorter
                    if ($dayStart->diff($dayEnd)->h * 60 + $dayStart->diff($dayEnd)->i % $slotDuration != 0) { //if the end time is not a multiple of the slot duration
                        $extraSlot = $dayStart->diff($dayEnd)->h * 60 + $dayStart->diff($dayEnd)->i % $slotDuration; // the extra time that is not a full slot
                        $numSlots -= 1; // the number of slots should be reduced by 1
                    }
                }
            }
            $tokengen = generateToken($creatorId, $name, $location, $slotDuration, $creationTime);
            $link = "http://cs.mcgill.ca/~gzu/socs_sisters/booking?token=" . urlencode($tokengen); 
            // Generate slots for the current day
            for ($i = 0; $i < $numSlots; $i++) {
                $slots[] = [
                    'start_date' => $currentDate->format('Y-m-d'), 
                    'start_time' => $currentDate->format('H:i:s'), 
                    'end_date' => $currentDate->format('Y-m-d'), 
                    'end_time' => $currentDate->add($duration)->format('H:i:s'), // Add duration to start time
                    'duration' => $slotDuration, 
                    'event_name' => $name,
                    'note' => $notes, 
                    'location' => $location, 
                    'max_people' => $participants, 
                    'creator_id' => $creatorId, 
                    'url' => $link 
                ];
            }
            if ($extraSlot > 0) {
                //create an extra slot with the remaining time
                //extra slot is one hour too much, so we need to reduce it by one hour
                $extraSlot = $extraSlot - 60;
                $slots[] = [
                    'start_date' => $currentDate->format('Y-m-d'), 
                    'start_time' => $currentDate->format('H:i:s'), 
                    'end_date' => $currentDate->format('Y-m-d'), 
                    'end_time' => $currentDate->add(new DateInterval('PT' . $extraSlot . 'M'))->format('H:i:s'), // Add duration to start time
                    'duration' => $extraSlot, 
                    'event_name' => $name,
                    'note' => $notes, 
                    'location' => $location, 
                    'max_people' => $participants, 
                    'creator_id' => $creatorId, 
                    'url' => $link 
                ];
            }
        }
        // Move to the next day
        $currentDate->modify('+1 day');
    }


    // Insert each slot into the database
    try {
        $database = new PDO('sqlite:ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $database->prepare(
            "INSERT INTO Events (creator_id, start_date, end_date, duration, start_time, end_time, event_name, note, location, max_people, url, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($slots as $slot) {
            $stmt->execute([
                $slot['creator_id'], 
                $slot['start_date'], 
                $slot['end_date'], 
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

        $eventDetails = [
            "name" => $name,
            "location" => $location,
            "start_time" => $startTime,
            "end_time" => $endTime,
            "participants" => $participants,
            "slotDuration" => $slotDuration,
            "notes" => $notes,
            "creator_id" => $creatorId,
            "email" => $creatorEmail, 
            "link" => $link, 
            "start_date" => $startDate,
            "end_date" => $endDate,
            "days" => $days,

            "slots" => $slots
        ];

        //if link is empty, then the event is not happening
        if (empty($link)) {
            echo json_encode(['success' => false, 'error' => 'Please check your input, for recurring events, the days selected need to be included in the time interval!']);
            exit;
        }

        $response = [
            "success" => true,
            "eventDetails" => $eventDetails,
        ];

        echo json_encode($response);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

?>