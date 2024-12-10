<!--PRIVATE PAGE-->
<?php
session_start();

if (!session_id()) {
  echo "Session failed to start.";
  exit;
} else {
  echo "Session started successfully. ID: " . session_id();
}

echo '<pre>';
print_r($_SESSION);
echo '</pre>';
//manually set the session id - FOR TESTING
$_SESSION['user_id'] = 1;

//Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not authenticated
    header("Location: ../login.html");
    exit;
}
else {
  //Redirect to the schedule page html if authenticated
  header("Location: ../dashboard.html");
}
?>