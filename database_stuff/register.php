<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $fname = trim($_POST["firstname"]);
    $lname = trim($_POST["lastname"]);
    $password = trim($_POST["password"]);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // validate input
    if (empty($email) || empty($fname) || empty($lname) || empty($password)) {
        echo "<script>
            alert('Please fill out all fields.');
            window.location.href = '../register';
        </script>";
        exit();
    }
    // Email validation before inserting into the database
    if (!preg_match('/@(mcgill\.ca|mail\.mcgill\.ca)$/', $email)) {
        echo "<script>
            alert('Email must end with @mcgill.ca or @mail.mcgill.ca.');
            window.location.href = '../register';
        </script>";
        exit();
    }

    // Password validation before inserting into the database
    if (strlen($password) < 10 || !preg_match('/[a-zA-Z]/', $password) || !preg_match('/\d/', $password)) {
        echo "<script>
            alert('Password must be at most 10 characters long, contain at least one letter, and at least one number.');
            window.location.href = '../register';
        </script>";
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
            echo "<script>
                alert('The email is already registered. Please use a different email.');
                window.location.href = '../register';
            </script>";
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
        echo "<script>
            alert('Registration successful! Please log in.');
            window.location.href = '../login';
        </script>";
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
