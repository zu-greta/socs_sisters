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
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $participants = $_POST['participants'] ?? '';
    $slotDuration = $_POST['slot'] ?? '';
    $calendar = $_POST['calendar'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $creatorId = 1; //TODO: Assuming logged-in user, replace with actual user ID !!!!! 

    // For recurring events, also capture additional details
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $days = isset($_POST['days']) ? explode(',', $_POST['days']) : []; 

    
    // Validate required fields
    if (empty($name) || empty($location) || empty($startDate) || empty($endDate) || empty($slotDuration) || empty($participants) || empty($calendar)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields for recurring events']);
        exit;
    }
    // TODO: Ensure start date is before end date - BUT ALLOW SAME DAY EVENTS - CHECK TIMES
    if (strtotime($startDate) >= strtotime($endDate)) {
        echo json_encode(['success' => false, 'error' => 'Start date must be before end date']);
        exit;
    }
    // TODO: Validate the start and end times 

    

    // Convert dates and times to DateTime objects
    $startDateObj = new DateTime($startDate);
    $endDateObj = new DateTime($endDate);
    $startTimeObj = new DateTime($startTime);
    $endTimeObj = new DateTime($endTime);

    $startDateObj = new DateTime($startDate . ' ' . $startTime); // Combine date and time
    $endDateObj = new DateTime($endDate . ' ' . $endTime); // Combine date and time


    // TODO: For recurring events, find all occurrences of the selected days - THE LOGIC HERE NEEDS TO CHANGE
    $slots = [];
    $interval = new DateInterval('PT' . $slotDuration . 'M'); // Slot duration in minutes (P = period, T = time)

    // Start slot with the combined date and time
    $currentSlotStart = clone $startDateObj;
    $currentSlotEnd = clone $startDateObj; // Use the same start time for end time, will be modified later

    while ($currentSlotStart < $endDateObj) {
        // Create the slot for the one-time event
        $slots[] = [
            'start_date' => $currentSlotStart->format('Y-m-d'),
            'start_time' => $currentSlotStart->format('H:i:s'),
            'end_date' => $currentSlotEnd->format('Y-m-d'),
            'end_time' => $currentSlotEnd->format('H:i:s'),
            'duration' => $slotDuration,
            'event_name' => $name,
            'note' => $notes,
            'location' => $location,
            'max_people' => $participants,
            'creator_id' => $creatorId
        ];

        // Move to the next slot
        $currentSlotStart->add($interval);
        $currentSlotEnd->add($interval);
    }

    // Loop over all days from start to end date and find the requested days
    // $slots = [];
    // $currentDate = clone $startDateObj;
    // while ($currentDate <= $endDateObj) {
    //     // Check if current day matches one of the selected days
    //     $dayOfWeek = $currentDate->format('l'); // e.g., Monday, Tuesday, etc.
    //     if (in_array($dayOfWeek, $days)) {
    //         // Create the slot for the recurring event
    //         $slots[] = [
    //             'start_date' => $currentDate->format('Y-m-d'),
    //             'start_time' => $startTimeObj->format('H:i:s'),
    //             'end_date' => $currentDate->format('Y-m-d'),
    //             'end_time' => $endTimeObj->format('H:i:s'),
    //             "slotDuration" => $slotDuration,
    //             'event_name' => $name,
    //             'note' => $notes,
    //             'location' => $location,
    //             'max_people' => $participants,
    //             'creator_id' => $creatorId
    //         ];
    //     }
    //     $currentDate->modify('+1 day'); // Move to the next day
    // }

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
            "participants" => $participants,
            "slotDuration" => $slotDuration,
            "calendar" => $calendar,
            "notes" => $notes,
            "creator_id" => $creatorId,
            "email" => "EMAILTEST", // Placeholder for now - get the user's email from the database
            "link" => "URLTEST", // Placeholder for now
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
