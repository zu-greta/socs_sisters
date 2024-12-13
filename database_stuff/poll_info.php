

// Function to fetch poll options for a given poll
function getPollOptions($poll_id) {
    // Connect to the database
    $database = new PDO('sqlite:./database_stuff/ssDB.sq3');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query the PollOptions table
    $stmt = $database->prepare("SELECT * FROM PollOptions WHERE poll_id = ?");
    $stmt->execute([$poll_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
