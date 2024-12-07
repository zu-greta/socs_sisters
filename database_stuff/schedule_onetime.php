<?php
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
    $start = $_POST['start_time'] ?? '';
    $end = $_POST['end_time'] ?? '';
    $participants = $_POST['participants'] ?? '';
    $slotDuration = $_POST['slot'] ?? '';
    $calendar = $_POST['calendar'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $creatorId = 1; //TODO: Assuming logged-in user, replace with actual user ID !!!!! 

    //TODO: CHECK Y TIME CANNOT BE VALIDATED
    if (empty($name) || empty($location) || empty($slotDuration) || empty($participants) || empty($calendar)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields for one-time events']); 
        //echo json_encode(['name' => $name, 'location' => $location, 'startTime' => $startTime, 'endTime' => $endTime, 'slotDuration' => $slotDuration, 'participants' => $participants, 'calendar' => $calendar]);   
        exit;
    }
    // TODO: Validate the start and end times - FIX THIS
    // if (strtotime($startTime) >= strtotime($endTime)) {
    //     echo json_encode(['success' => false, 'error' => 'Start time must be before end time']);
    //     exit;
    // }

    // split up the time and date
    $startTime = explode('T', $start)[1];
    $startDate = explode('T', $start)[0];
    $endTime = explode('T', $end)[1];
    $endDate = explode('T', $end)[0];

    $startDateObj = new DateTime($start);
    $endDateObj = new DateTime($end);


    // TODO: For one-time events, calculate the slots (duration-based) - THE LOGIC HERE NEEDS TO CHANGE
    $slots = [];
    $currentDate = clone $startDateObj;
    $duration = new DateInterval('PT' . $slotDuration . 'M');
    $numSlots = $startDateObj->diff($endDateObj)->days * 24 * 60 / $slotDuration;
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
            'creator_id' => $creatorId
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

        // Assign the URL value FOR TESTING ONLY - ACTUALLY GENERATE ONE
        $url_placeholder = "URLTEST"; 

        //Insert each slot
        foreach ($slots as $slot) {
            $stmt->execute([
                $slot['creator_id'], 
                $slot['start_date'], 
                $slot['start_date'], // Same date for start and end
                $slot['duration'], 
                $slot['start_time'], 
                $slot['end_time'], 
                $slot['event_name'], 
                $slot['note'], 
                $slot['location'], 
                $slot['max_people'],
                $url_placeholder    // Placeholder for now
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
            "calendar" => $calendar,
            "notes" => $notes,
            "creator_id" => $creatorId,
            "email" => "EMAILTEST", // Placeholder for now - get the user's email from the database
            "link" => "URLTEST", // Placeholder for now - get the URL from the database
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
