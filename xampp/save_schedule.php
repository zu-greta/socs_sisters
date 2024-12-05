<?php
// Connect to the SQLite database
class socssistersDB extends SQLite3 {
    function __construct() {
        $this->open('socssistersDB.db');
    }
}

$db = new socssistersDB();

if (!$db) {
    echo $db->lastErrorMsg();
} else {
    // Get the form data
    $name = $_POST['name'];
    $location = $_POST['location'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $participants = $_POST['participants'];
    $slot = $_POST['slot'];
    $calendar = $_POST['calendar'];
    $notes = $_POST['notes'];

    // Prepare the SQL statement to insert the data into the SCHEDULEEVENTS table
    $sql = "INSERT INTO SCHEDULEEVENTS (NAME, LOCATION, STARTDATE, ENDDATE, PARTICIPANTS, SLOT, CALENDAR, NOTES)
            VALUES ('$name', '$location', '$start_time', '$end_time', '$participants', '$slot', '$calendar', '$notes')";

    $ret = $db->exec($sql);
    if (!$ret) {
        echo $db->lastErrorMsg();
    } else {
        // Redirect with the event details
        header("Location: scheduling.html?event_name=$name&location=$location&start_time=$start_time&end_time=$end_time&participants=$participants&slot=$slot&calendar=$calendar&notes=$notes");
        exit();
    }

    // Close the database connection
    $db->close();
}
?>
