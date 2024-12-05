<?php
try {
    // Connect to SQLite database
    $dbFile = 'socssistersDB.db'; // Change this if your database file is elsewhere
    $conn = new SQLite3($dbFile);
    echo "Connected successfully to SQLite database: $dbFile\n";

    // Query to list all tables in the database
    $query = "SELECT name FROM sqlite_master WHERE type='table';";
    $result = $conn->query($query);

    echo "\nTables in the database:\n";
    $tables = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "- " . $row['name'] . "\n";
        $tables[] = $row['name'];
    }

    if (empty($tables)) {
        echo "No tables found in the database.\n";
    } else {
        echo "\nContents of each table:\n";
        foreach ($tables as $table) {
            echo "\nTable: $table\n";
            $query = "SELECT * FROM $table";
            $result = $conn->query($query);

            // Fetch and display table contents
            $columnsPrinted = false;
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!$columnsPrinted) {
                    echo implode(" | ", array_keys($row)) . "\n";
                    echo str_repeat("-", 50) . "\n";
                    $columnsPrinted = true;
                }
                echo implode(" | ", $row) . "\n";
            }
            if (!$columnsPrinted) {
                echo "No data found in this table.\n";
            }
        }
    }

    // Close the connection
    $conn->close();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
