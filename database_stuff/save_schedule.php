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
    // $sql = "INSERT INTO SCHEDULEEVENTS (NAME, LOCATION, STARTDATE, ENDDATE, PARTICIPANTS, SLOT, CALENDAR, NOTES)
    //         VALUES ('$name', '$location', '$start_time', '$end_time', '$participants', '$slot', '$calendar', '$notes')";

    $stmt = $db->prepare('INSERT INTO SCHEDULEEVENTS (NAME, LOCATION, STARTDATE, ENDDATE, PARTICIPANTS, SLOT, CALENDAR, NOTES) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bindValue(1, $name, SQLITE3_TEXT);
    $stmt->bindValue(2, $location, SQLITE3_TEXT);
    $stmt->bindValue(3, $start_time, SQLITE3_TEXT);
    $stmt->bindValue(4, $end_time, SQLITE3_TEXT);
    $stmt->bindValue(5, $participants, SQLITE3_INTEGER);
    $stmt->bindValue(6, $slot, SQLITE3_INTEGER);
    $stmt->bindValue(7, $calendar, SQLITE3_TEXT);
    $stmt->bindValue(8, $notes, SQLITE3_TEXT);
    //$stmt->execute();

    //$ret = $db->exec($sql);
    $ret = $stmt->execute();
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
