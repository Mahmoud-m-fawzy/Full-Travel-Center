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

// Check if the session has a logged-in customer (based on id_customer)
if (isset($_SESSION['id_customer'])) {
    // Get the customer ID from session
    $id_customer = $_SESSION['id_customer'];

    // Check if the customer exists in the database
    $checkCustomerQuery = "SELECT * FROM customer WHERE id_customer = ?";
    $stmtCustomer = $conn->prepare($checkCustomerQuery);
    $stmtCustomer->bind_param("i", $id_customer);
    $stmtCustomer->execute();
    $customerResult = $stmtCustomer->get_result();

    // Check if the customer is found
    if ($customerResult->num_rows > 0) {
        // Update the login status to 0 (logged out)
        $updateLoginStatus = "UPDATE customer SET is_logged_in = 0 WHERE id_customer = ?";
        $stmtUpdateCustomer = $conn->prepare($updateLoginStatus);
        $stmtUpdateCustomer->bind_param("i", $id_customer);
        $stmtUpdateCustomer->execute();
        $stmtUpdateCustomer->close();

        // Close the statement for customer check
        $stmtCustomer->close();

        // Destroy the session to log the customer out
        session_unset();
        session_destroy();

        // Redirect to home page after successful logout
        header("Location: home.html");
        exit();
    } else {
        // If the customer is not found, redirect to login page
        header("Location: login.html");
        exit();
    }
} else {
    // If no customer is logged in, redirect to login page
    header("Location: login.html");
    exit();
}

// Close the database connection
$conn->close();
?>
