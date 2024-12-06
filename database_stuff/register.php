<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $password = $_POST['password'];

    // Validate fields and hash password
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format");
    }

    if (strlen($password) < 10 || !preg_match('/\d/', $password) || !preg_match('/[a-zA-Z]/', $password)) {
        die("Password does not meet criteria");
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Save to database
    try {
        $database = new PDO('sqlite:ssDB.sq3');
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $database->prepare("INSERT INTO Users (fname, lname, email, password_hash) VALUES (:fname, :lname, :email, :password_hash)");
        $stmt->execute([
            ':fname' => $firstname,
            ':lname' => $lastname,
            ':email' => $email,
            ':password_hash' => $passwordHash
        ]);

        echo "Registration successful!";
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            echo "Email already exists.";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>
