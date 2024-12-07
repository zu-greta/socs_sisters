<!--PRIVATE PAGE-->
<?php
session_start();
header("Location: ../scheduling.html"); //FOR NOW JUST REDIRECT TO SCHEDULING PAGE

//IDK Y THIS DOESNT WORK RN

// if (!session_id()) {
//   echo "Session failed to start.";
//   exit;
// } else {
//   echo "Session started successfully. ID: " . session_id();
// }

// echo '<pre>';
// print_r($_SESSION);
// echo '</pre>';

// //Check if the user is logged in
// if (!isset($_SESSION['user_id'])) {
//     // Redirect to the login page if not authenticated
//     //header("Location: ../login.html");
//     header("Location: ../register.html");
//     exit;
// }
// else {
//   //Redirect to the schedule page html if authenticated
//   header("Location: ../scheduling.html");
// }
?>