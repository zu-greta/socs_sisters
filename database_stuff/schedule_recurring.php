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

//temp code FIGURE OUT CALCULATIONS 

// Handle POST request to schedule recurring or one-time events
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate the form data
    $name = $_POST['name'] ?? '';
    $location = $_POST['location'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $participants = $_POST['participants'] ?? '';
    $slotDuration = $_POST['slot'] ?? '';
    //$calendar = $_POST['calendar'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $creatorId = 1; //TODO: Assuming logged-in user, replace with actual user ID !!!!!

    // For recurring events, also capture additional details
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    //$days = isset($_POST['days']) ? explode(',', $_POST['days']) : []; 
    $days = isset($_POST['day']) ? $_POST['day'] : [];
    
    // Validate required fields
    if (empty($name) || empty($location) || empty($startDate) || empty($endDate) || empty($participants) || empty($days)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields for recurring events']);
        exit;
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
            // Calculate the number of slots for the current day
            $dayStart = clone $startTimeObj; // Use the provided start time
            $dayEnd = clone $endTimeObj; // Use the provided end time
            if ($slotDuration < 1) {
                $numSlots = 1;
            } else {
                //numslots = (endDateObj - startDateObj / duration)
                $numSlots = ($dayStart->diff($dayEnd)->h * 60 + $dayStart->diff($dayEnd)->i) / $slotDuration;
            }
            $link = "http://cs.mcgill.ca/~gzu/socs_sisters/booking/event?creator_id=" . urlencode($creatorId) . "&eventName=" . urlencode($name) . "&eventDuration=" . urlencode($slotDuration) . "&eventLocation=" . urlencode($location); // TODO replace with actual link
            // Generate slots for the current day
            for ($i = 0; $i < $numSlots; $i++) {
                $slots[] = [
                    'start_date' => $currentDate->format('Y-m-d'), // Use the current date
                    'start_time' => $currentDate->format('H:i:s'), 
                    'end_date' => $currentDate->format('Y-m-d'), // Same day as start
                    'end_time' => $currentDate->add($duration)->format('H:i:s'), // Add duration to start time
                    'duration' => $slotDuration, // Duration in minutes
                    'event_name' => $name, // Event name
                    'note' => $notes, // Event notes
                    'location' => $location, // Event location
                    'max_people' => $participants, // Max people allowed
                    'creator_id' => $creatorId, // Creator ID
                    'url' => $link // Event link
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
                $slot['end_date'], // Same date for start and end
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
            "participants" => $participants,
            "slotDuration" => $slotDuration,
            "calendar" => "calendar REMOVE THIS FROM THE FRONTEND HTML",
            "notes" => $notes,
            "creator_id" => $creatorId,
            "email" => $creatorEmail, 
            "link" => $link, 
            "start_date" => $startDate,
            "end_date" => $endDate,
            "days" => $days,
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
