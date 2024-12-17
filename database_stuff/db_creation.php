<?php

//USER GUIDE: run in browser http://localhost/comp307/socs_sisters/database_stuff/db_creation.php to create the database and tables - itll clean it up and create a new one without any data

try {


    // Connect to SQLite database (creates the file if it doesn't exist)
    $database = new PDO('sqlite:ssDB.sq3'); 

    // Enable exceptions for errors
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Disable foreign key constraints
    $database->exec("PRAGMA foreign_keys = OFF;");

    // Drop tables if they exist (Add your table names here)
    $tablesToDrop = ['Users', 'Events', 'Bookings', 'TimeRequests', 'MeetingPolls', 'PollOptions', 'PollVotes', 'Sessions', 'Tokens']; // Replace with your actual table names
    foreach ($tablesToDrop as $table) {
        $dropQuery = "DROP TABLE IF EXISTS $table";
        $database->exec($dropQuery);
    }


    // Re-enable foreign key constraints
    $database->exec("PRAGMA foreign_keys = ON;");



    // Read the SQL schema from a file
    $sql = file_get_contents('tables.sql');

    // Check if file_get_contents successfully read the file
    if ($sql === false) {
        throw new Exception("Failed to read the SQL schema file.");
    }

    // Execute the SQL schema
    $database->exec($sql);

    echo "Database and tables created successfully!";

    // //comment out this section if you dont want to load the data
    // // load some data into the tables
    // $sql = file_get_contents('data.sql');
    // // Check if file_get_contents successfully read the file
    // if ($sql === false) {
    //     throw new Exception("Failed to read the SQL data file.");
    // }
    // // Execute the SQL schema
    // $database->exec($sql);

    // echo "Data loaded successfully!";
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
