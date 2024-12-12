<?php
session_start();

// echo "Hello, World!";
// Get requested route
$route = $_GET['route'] ?? 'landing';
$routes = [
    //public pages
    'landing' => 'frontend_stuff/public_pages/landing.php',
    'login' => 'frontend_stuff/public_pages/login.php',
    'register' => 'frontend_stuff/public_pages/register.php',

    //private pages
    'dashboard' => 'frontend_stuff/private_pages/dashboard.php',
    'createPoll' => 'frontend_stuff/private_pages/createPoll.php',
    'resultPoll' => 'frontend_stuff/private_pages/resultPoll.php',
    'scheduling' => 'frontend_stuff/private_pages/scheduling.php', 
    'history' => 'frontend_stuff/private_pages/history.php',
    'preferences' => 'frontend_stuff/private_pages/preferences.php',
    'logout' => 'frontend_stuff/private_pages/logout.php',
    'viewTimeRequest' => 'frontend_stuff/private_pages/viewTimeRequest.php',
    'makeTimeRequest' => 'frontend_stuff/private_pages/makeTimeRequest.php',

    //secret pages
    'booking' => 'frontend_stuff/public_pages/booking.php',
    'votePoll' => 'frontend_stuff/public_pages/votePoll.php',

    //TODO: check
];

// Check if route exists
if (!array_key_exists($route, $routes)) {
    // 404 Not Found
    http_response_code(404);
    echo "404 Not Found";
    exit;
} else {
    $file = $routes[$route];
    if (strpos($file, 'private_pages') !== false && !loggedIn()) {
        requireLogin();
    }
    require $file; // Include the requested page
}

// helper functions for auth
function loggedIn() {
    //PLACEHOLDER TO BE FIXED
    //$_SESSION['user_id'] = 1;
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    // Redirect to the login page if not authenticated
    header("Location: login");
    exit;
}

?>