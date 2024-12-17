<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $fname = trim($_POST["firstname"]);
    $lname = trim($_POST["lastname"]);
    $password = trim($_POST["password"]);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // validate input
    if (empty($email) || empty($fname) || empty($lname) || empty($password)) {
        // echo "<script>
        //     alert('Please fill out all fields.');
        //     window.location.href = '../register';
        // </script>";
        echo json_encode(['success' => false, 'error' => 'Please fill out all fields']);
        $errorMessage = "Please fill out all fields";
        echo "
            <html>
            <head>
                <style>
                    /* Modal Background */
                    .modal {
                        display: block;
                        position: fixed;
                        z-index: 1000;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.6);
                    }

                    /* Modal Content */
                    .modal-content {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        background-color: #ffffff;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        max-width: 400px;
                        text-align: center;
                        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                        font-family: Arial, sans-serif;
                    }

                    .modal-content h2 {
                        color: black;
                        font-size: 1.5em;
                        margin-bottom: 20px;
                    }

                    .close-btn {
                        cursor: pointer;
                        color: black;
                        font-size: 1.5em;
                        font-weight: bold;
                        position: absolute;
                        top: 10px;
                        right: 15px;
                    }

                    .close-btn:hover {
                        color: #ed1b2f;
                    }

                    .modal-content p {
                        font-size: 1.1em;
                        margin: 10px 0;
                    }

                    .modal-content button {
                        background-color: #ed1b2f;
                        color: white;
                        border: none;
                        padding: 10px 15px;
                        font-size: 1em;
                        border-radius: 5px;
                        cursor: pointer;
                    }

                    .modal-content button:hover {
                        background-color: #ed1b2f;
                    }
                    button {
                    padding: 10px 30px;
                    font-weight: 500;
                    border-radius: 6px;
                    font-size: 0.9em;
                    cursor: pointer;
                    transition: 0.2s ease;
                    }

                    button.primary {
                    background-color: #ae0e1d;
                    border: 3px solid #ae0e1d;
                    color : white;
                    }

                    button.primary:hover {
                    background-color: #ed1b2f;
                    border: 3px solid #ed1b2f;
                    }
                </style>
            </head>
            <body>
                <!-- Error Modal -->
                <div id='error-modal' class='modal'>
                    <div class='modal-content'>
                        <h2>Error</h2>
                        <p>$errorMessage</p>
                        </br>
                        <button class = 'primary' onclick='redirectToRegister()'>Back to Register</button>
                    </div>
                </div>

                <script>
                    function closeModal() {
                        document.getElementById('error-modal').style.display = 'none';
                    }
                    function redirectToRegister() {
                        window.location.href = '../register';
                    }
                </script>
            </body>
            </html>
        ";
        exit();
    }
    // Email validation before inserting into the database
    if (!preg_match('/@(mcgill\.ca|mail\.mcgill\.ca)$/', $email)) {
        // echo "<script>
        //     alert('Email must end with @mcgill.ca or @mail.mcgill.ca.');
        //     window.location.href = '../register';
        // </script>";
        echo json_encode(['success' => false, 'error' => 'Email must end with @mcgill.ca or @mail.mcgill.ca']);
        $errorMessage = "Email must end with @mcgill.ca or @mail.mcgill.ca";
        echo "
            <html>
            <head>
                <style>
                    /* Modal Background */
                    .modal {
                        display: block;
                        position: fixed;
                        z-index: 1000;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.6);
                    }

                    /* Modal Content */
                    .modal-content {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        background-color: #ffffff;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        max-width: 400px;
                        text-align: center;
                        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                        font-family: Arial, sans-serif;
                    }

                    .modal-content h2 {
                        color: black;
                        font-size: 1.5em;
                        margin-bottom: 20px;
                    }

                    .close-btn {
                        cursor: pointer;
                        color: black;
                        font-size: 1.5em;
                        font-weight: bold;
                        position: absolute;
                        top: 10px;
                        right: 15px;
                    }

                    .close-btn:hover {
                        color: #ed1b2f;
                    }

                    .modal-content p {
                        font-size: 1.1em;
                        margin: 10px 0;
                    }

                    .modal-content button {
                        background-color: #ed1b2f;
                        color: white;
                        border: none;
                        padding: 10px 15px;
                        font-size: 1em;
                        border-radius: 5px;
                        cursor: pointer;
                    }

                    .modal-content button:hover {
                        background-color: #ed1b2f;
                    }
                    button {
                    padding: 10px 30px;
                    font-weight: 500;
                    border-radius: 6px;
                    font-size: 0.9em;
                    cursor: pointer;
                    transition: 0.2s ease;
                    }

                    button.primary {
                    background-color: #ae0e1d;
                    border: 3px solid #ae0e1d;
                    color : white;
                    }

                    button.primary:hover {
                    background-color: #ed1b2f;
                    border: 3px solid #ed1b2f;
                    }
                </style>
            </head>
            <body>
                <!-- Error Modal -->
                <div id='error-modal' class='modal'>
                    <div class='modal-content'>
                        <h2>Error</h2>
                        <p>$errorMessage</p>
                        </br>
                        <button class = 'primary' onclick='redirectToRegister()'>Back to Register</button>
                    </div>
                </div>

                <script>
                    function closeModal() {
                        document.getElementById('error-modal').style.display = 'none';
                    }
                    function redirectToRegister() {
                        window.location.href = '../register';
                    }
                </script>
            </body>
            </html>
        ";
        exit();
    }

    // Password validation before inserting into the database
    if (strlen($password) < 10 || !preg_match('/[a-zA-Z]/', $password) || !preg_match('/\d/', $password)) {
        // echo "<script>
        //     alert('Password must be at most 10 characters long, contain at least one letter, and at least one number.');
        //     window.location.href = '../register';
        // </script>";
        echo json_encode(['success' => false, 'error' => 'Password must be at most 10 characters long, contain at least one letter, and at least one number']);
        $errorMessage = "Password must be at most 10 characters long, contain at least one letter, and at least one number";
        echo "
            <html>
            <head>
                <style>
                    /* Modal Background */
                    .modal {
                        display: block;
                        position: fixed;
                        z-index: 1000;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.6);
                    }

                    /* Modal Content */
                    .modal-content {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        background-color: #ffffff;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        max-width: 400px;
                        text-align: center;
                        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                        font-family: Arial, sans-serif;
                    }

                    .modal-content h2 {
                        color: black;
                        font-size: 1.5em;
                        margin-bottom: 20px;
                    }

                    .close-btn {
                        cursor: pointer;
                        color: black;
                        font-size: 1.5em;
                        font-weight: bold;
                        position: absolute;
                        top: 10px;
                        right: 15px;
                    }

                    .close-btn:hover {
                        color: #ed1b2f;
                    }

                    .modal-content p {
                        font-size: 1.1em;
                        margin: 10px 0;
                    }

                    .modal-content button {
                        background-color: #ed1b2f;
                        color: white;
                        border: none;
                        padding: 10px 15px;
                        font-size: 1em;
                        border-radius: 5px;
                        cursor: pointer;
                    }

                    .modal-content button:hover {
                        background-color: #ed1b2f;
                    }
                    button {
                    padding: 10px 30px;
                    font-weight: 500;
                    border-radius: 6px;
                    font-size: 0.9em;
                    cursor: pointer;
                    transition: 0.2s ease;
                    }

                    button.primary {
                    background-color: #ae0e1d;
                    border: 3px solid #ae0e1d;
                    color : white;
                    }

                    button.primary:hover {
                    background-color: #ed1b2f;
                    border: 3px solid #ed1b2f;
                    }
                </style>
            </head>
            <body>
                <!-- Error Modal -->
                <div id='error-modal' class='modal'>
                    <div class='modal-content'>
                        <h2>Error</h2>
                        <p>$errorMessage</p>
                        </br>
                        <button class = 'primary' onclick='redirectToRegister()'>Back to Register</button>
                    </div>
                </div>

                <script>
                    function closeModal() {
                        document.getElementById('error-modal').style.display = 'none';
                    }
                    function redirectToRegister() {
                        window.location.href = '../register';
                    }
                </script>
            </body>
            </html>
        ";
        exit();
    }


    try {
        // Connect to the database
        $database = new PDO('sqlite:ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if the email already exists
        $checkStmt = $database->prepare("SELECT COUNT(*) FROM Users WHERE email = :email");
        $checkStmt->execute([':email' => $email]);
        $emailExists = $checkStmt->fetchColumn() > 0;

        if ($emailExists) {
            // Redirect back to the registration page with a pop-up
            // echo "<script>
            //     alert('The email is already registered. Please use a different email.');
            //     window.location.href = '../register';
            // </script>";
            echo json_encode(['success' => false, 'error' => 'The email is already registered. Please use a different email']);
            $errorMessage = "The email is already registered. Please use a different email";
            echo "
            <html>
            <head>
                <style>
                    /* Modal Background */
                    .modal {
                        display: block;
                        position: fixed;
                        z-index: 1000;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.6);
                    }

                    /* Modal Content */
                    .modal-content {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        background-color: #ffffff;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        max-width: 400px;
                        text-align: center;
                        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                        font-family: Arial, sans-serif;
                    }

                    .modal-content h2 {
                        color: black;
                        font-size: 1.5em;
                        margin-bottom: 20px;
                    }

                    .close-btn {
                        cursor: pointer;
                        color: black;
                        font-size: 1.5em;
                        font-weight: bold;
                        position: absolute;
                        top: 10px;
                        right: 15px;
                    }

                    .close-btn:hover {
                        color: #ed1b2f;
                    }

                    .modal-content p {
                        font-size: 1.1em;
                        margin: 10px 0;
                    }

                    .modal-content button {
                        background-color: #ed1b2f;
                        color: white;
                        border: none;
                        padding: 10px 15px;
                        font-size: 1em;
                        border-radius: 5px;
                        cursor: pointer;
                    }

                    .modal-content button:hover {
                        background-color: #ed1b2f;
                    }
                    button {
                    padding: 10px 30px;
                    font-weight: 500;
                    border-radius: 6px;
                    font-size: 0.9em;
                    cursor: pointer;
                    transition: 0.2s ease;
                    }

                    button.primary {
                    background-color: #ae0e1d;
                    border: 3px solid #ae0e1d;
                    color : white;
                    }

                    button.primary:hover {
                    background-color: #ed1b2f;
                    border: 3px solid #ed1b2f;
                    }
                </style>
            </head>
            <body>
                <!-- Error Modal -->
                <div id='error-modal' class='modal'>
                    <div class='modal-content'>
                        <h2>Error</h2>
                        <p>$errorMessage</p>
                        </br>
                        <button class = 'primary' onclick='redirectToRegister()'>Back to Register</button>
                    </div>
                </div>

                <script>
                    function closeModal() {
                        document.getElementById('error-modal').style.display = 'none';
                    }
                    function redirectToRegister() {
                        window.location.href = '../register';
                    }
                </script>
            </body>
            </html>
        ";
            exit();
        }

        // If email is not registered, insert the new user
        $stmt = $database->prepare("INSERT INTO Users (fname, lname, email, password_hash) VALUES (:fname, :lname, :email, :password_hash)");
        $stmt->execute([
            ':fname' => $fname,
            ':lname' => $lname,
            ':email' => $email,
            ':password_hash' => $passwordHash
        ]);

        // Registration successful, redirect to login page
        // echo "<script>
        //     alert('Registration successful! Please log in.');
        //     window.location.href = '../login';
        // </script>";
        echo json_encode(['success' => true]);
        echo "
            <html>
            <head>
                <style>
                    /* Modal Background */
                    .modal {
                        display: block;
                        position: fixed;
                        z-index: 1000;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.6);
                    }

                    /* Modal Content */
                    .modal-content {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        background-color: #ffffff;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        max-width: 400px;
                        text-align: center;
                        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                        font-family: Arial, sans-serif;
                    }

                    .modal-content h2 {
                        color: black;
                        font-size: 1.5em;
                        margin-bottom: 20px;
                    }

                    .close-btn {
                        cursor: pointer;
                        color: black;
                        font-size: 1.5em;
                        font-weight: bold;
                        position: absolute;
                        top: 10px;
                        right: 15px;
                    }

                    .close-btn:hover {
                        color: #ed1b2f;
                    }

                    .modal-content p {
                        font-size: 1.1em;
                        margin: 10px 0;
                    }

                    .modal-content button {
                        background-color: #ed1b2f;
                        color: white;
                        border: none;
                        padding: 10px 15px;
                        font-size: 1em;
                        border-radius: 5px;
                        cursor: pointer;
                    }

                    .modal-content button:hover {
                        background-color: #ed1b2f;
                    }
                    button {
                    padding: 10px 30px;
                    font-weight: 500;
                    border-radius: 6px;
                    font-size: 0.9em;
                    cursor: pointer;
                    transition: 0.2s ease;
                    }

                    button.primary {
                    background-color: #ae0e1d;
                    border: 3px solid #ae0e1d;
                    color : white;
                    }

                    button.primary:hover {
                    background-color: #ed1b2f;
                    border: 3px solid #ed1b2f;
                    }

                </style>
            </head>
            <body>
                <!-- Success Modal -->
                <div id='success-modal' class='modal'>
                    <div class='modal-content'>
                        <h2>Success</h2>
                        <p>Registration successful! Please log in.</p>
                        </br>
                        <button class = 'primary' onclick='redirectToLogin()'>Go to Login</button>
                    </div>
                </div>

                <script>
                    function closeModal() {
                        document.getElementById('success-modal').style.display = 'none';
                    }
                    function redirectToLogin() {
                        window.location.href = '../login';
                    }
                </script>
            </body>
            </html>
        ";
        exit();
    } catch (PDOException $e) {
        // Handle database errors
        echo "<script>
            alert('Error: " . addslashes($e->getMessage()) . "');
            window.location.href = '../register';
        </script>";
        exit();
    }
}
?>
