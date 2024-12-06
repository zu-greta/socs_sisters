<?php
try {


    // Connect to SQLite database (creates the file if it doesn't exist)
    $database = new PDO('sqlite:ssDB.sq3'); // Correct way to instantiate PDO for SQLite

    // Enable exceptions for errors
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Disable foreign key constraints
    $database->exec("PRAGMA foreign_keys = OFF;");

    // Drop tables if they exist (Add your table names here)
    $tablesToDrop = ['Users', 'Events', 'Bookings', 'TimeRequests', 'MeetingPolls', 'PollOptions', 'PollVotes']; // Replace with your actual table names
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
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
