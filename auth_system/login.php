<?php
require 'force_https.php';
session_start();
require 'db_connect.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate username and password
    if (empty($username) || empty($password)) {
        echo "Username and password are required.";
        exit();
    }

    // Sanitize username
    $username = filter_var($username, FILTER_SANITIZE_STRING);

    // Prepare and bind
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);
            
            // Store user information in session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            
            // Set session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1); // if using HTTPS
            ini_set('session.use_only_cookies', 1);
            
            echo "Login successful! Welcome, " . htmlspecialchars($username) . ".";
        } else {
            echo "Invalid username or password.";
        }
    } else {
        echo "Invalid username or password.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and bind
$stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

// Execute query
if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

//this are added if code dont give good result try remove these to the end
// Bind result variables
if (!$stmt->bind_result($id, $hashed_password)) {
    die("Error binding result variables: ". $stmt->error);
}

// Fetch result
if (!$stmt->fetch()) {
    die("Error fetching result: ". $stmt->error);
}

// Close statement
if (!$stmt->close()) {
    die("Error closing statement: ". $stmt->error);
}

// Close connection
if (!$conn->close()) {
    die("Error closing connection: ". $conn->error);
}
?>