<?php
session_start();

// Simulate user credentials (in a real scenario, use a database)
// TEMPLATE CODE REPLACE WITH DB STUFF
$valid_username = "admin";
$valid_password = "passpass";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate credentials
    if ($username === $valid_username && $password === $valid_password) {
        // Set session variable
        $_SESSION['user_id'] = $username;

        // Redirect to the protected page - THIS WILL BE THE LANDING PAGE
        header("Location: schedule_sessioncheck.php");
        exit;
    } else {
        $error_message = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login Page</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
        }

        .container {
            display: flex;
            width: 100%;
            height: 100%;
        }

        /* Left Section Styles */
        .left-section {
            flex: 1;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .left-section img {
            width: 60%;
            max-width: 300px;
        }

        .left-section h3 {
            margin: 20px 0;
            font-size: 1.5rem;
            text-align: center;
        }

        .left-section p {
            text-align: center;
            font-size: 0.9rem;
            color: #555;
            max-width: 80%;
        }

        /* Right Section Styles */
        .right-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .right-section img {
            width: 80px;
            margin-bottom: 20px;
        }

        .right-section h2 {
            margin-bottom: 10px;
        }

        .right-section p {
            margin-bottom: 20px;
            color: #555;
        }

        form {
            width: 80%;
            max-width: 300px;
        }

        form label {
            display: block;
            margin-bottom: 5px;
        }

        form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .flex-container {
            display: flex;
            align-items: center; /* Vertically aligns checkbox and text */
            justify-content: space-between; /* Adds space between "Remember Me" and "Forgot Password?" */
            margin-bottom: 15px; /* Keeps consistent spacing below */
        }

        .flex-container label {
            display: flex;
            align-items: center; /* Aligns the checkbox and text in the label */
        }

        .flex-container input[type="checkbox"] {
            margin-right: 5px; /* Adds space between the checkbox and "Remember Me" text */
        }

        .button {
            width: 100%;
            padding: 10px;
            background-color: #ae0e1d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .bottom-text {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #555;
            text-align: center;
        }

        .bottom-text a {
            color: #007BFF;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Left Section -->
        <div class="left-section">
            <img src="png/placeholder.png" alt="Skeleton">
            <h3>Student-favorite Tool of 2024 💯</h3>
            <p>
                "SOC-cessful Schedule is much a life changer for my teaching career." <br>
                -- Prof. Joseph Vybhial
            </p>
        </div>

        <!-- Right Section -->
        <div class="right-section">
            <img src="png/logo.png" alt="SOC-cessful Schedule Logo">
            <h2>Login to your Account</h2>
            <p>Check your upcoming schedules with us!</p>
            <form method="POST" action="">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>


                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" name="remember" style="margin-right: 5px;"> Remember Me
                    </label>
                    <a href="#" style="font-size: 0.9rem; color: #007BFF; text-decoration: none;">Forgot Password?</a>
                </div>


                <button type="button" class="button" onclick="redirectToDashboard()">Login</button>
            </form>
            <p class="bottom-text">
                Not Registered Yet? <a href="register.html">Create an account</a>
            </p>
        </div>
    </div>

    <script>
        function redirectToDashboard() {
            // Redirect user to the dashboard page after login
            //window.location.href = "dashboard.html";
            // GRETA: TESTING THE PHP PRIVATE PAGE THINGY
            window.location.href = "schedule_sessioncheck.php";
        }
    </script>

</body>
</html>
