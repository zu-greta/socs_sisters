<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate the form data
    $fn = $_POST['fn'] ?? '';
    $ln = $_POST['ln'] ?? '';
    
    
    if (empty($fn) || empty($ln)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields']);
        // echo "<script>
        //     alert('Please fill in all required fields');
            
        //     window.location.href = '../preferences';
        // </script>";
        $errorMessage = "Please fill in all required fields";
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
                    color: white;
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
                        <button class = 'primary' onclick='redirectToPreferences()'>Back to Preferences</button>
                    </div>
                </div>

                <script>
                    function closeModal() {
                        document.getElementById('error-modal').style.display = 'none';
                    }
                    function redirectToPreferences() {
                        window.location.href = '../preferences';
                    }
                </script>
            </body>
            </html>
        ";
        exit;
    }

    // Insert each slot into the database
    try {
        $database = new PDO('sqlite:ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get userID
        $stmt = $database->prepare("SELECT user_id FROM Sessions WHERE session_token = ?");
        $stmt->execute([$_COOKIE['auth_key']]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$session) {
            header("Location: login");
        }
        $userID = $session['user_id'];

        $stmt = $database->prepare(
            "UPDATE Users SET fname = ? WHERE user_id = ?"
        );
        $stmt->execute([$fn, $userID]);

        $stmt = $database->prepare(
            "UPDATE Users SET lname = ? WHERE user_id = ?"
        );
        $stmt->execute([$ln, $userID]);

        $response = [
            "success" => true,
        ];

        // Send JSON response
        echo json_encode($response);
        //redirect back to the dashboard
        header('Location: ../preferences');
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}

?>