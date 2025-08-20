<?php
// Start the session
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_project";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the session has a logged-in user (only for owner)
if (isset($_SESSION['user_id'])) {
    // Get the user ID from session
    $user_id = $_SESSION['user_id'];

    // Check if the user is an owner (by checking if owner_id exists)
    $checkOwnerQuery = "SELECT * FROM owner WHERE owner_id = ?";
    $stmtOwner = $conn->prepare($checkOwnerQuery);
    $stmtOwner->bind_param("i", $user_id);
    $stmtOwner->execute();
    $ownerResult = $stmtOwner->get_result();

    // Check if the user is found in the owner table
    if ($ownerResult->num_rows > 0) {
        // Set the owner status to logged out (is_login = 0)
        $updateLoginStatus = "UPDATE owner SET is_login = 0 WHERE owner_id = ?";
        $stmtUpdateOwner = $conn->prepare($updateLoginStatus);
        $stmtUpdateOwner->bind_param("i", $user_id);
        $stmtUpdateOwner->execute();
        $stmtUpdateOwner->close();

        // Close the statement
        $stmtOwner->close();

        // Destroy the session to log the owner out
        session_unset();
        session_destroy();

        // Redirect to login page
        header("Location: login.html");
        exit();
    } else {
        // If the user is not an owner, redirect to login page
        header("Location: login.html");
        exit();
    }
} else {
    // If no user is logged in, redirect to login page
    header("Location: login.html");
    exit();
}

// Close the database connection
$conn->close();
?>
