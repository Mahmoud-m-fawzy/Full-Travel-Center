<?php
// Start session to manage user sessions
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

// Handle form submission for login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Collect and sanitize form data
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if the email is owner@gmail.com and password is password123
    if ($email === 'owner@gmail.com' && $password === 'password123') {
        // Set session for the owner
        $_SESSION['owner_id'] = 'owner';  // Store a session value for the owner

        // Retrieve the owner_id from the owner_email table using the provided email
        $stmt = $conn->prepare("SELECT owner_id FROM owner_email WHERE owner_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch the owner_id
            $row = $result->fetch_assoc();
            $owner_id = $row['owner_id'];

            // Update the is_login field in the owner table for this owner_id
            $updateOwnerStatus = "UPDATE owner SET is_login = 1 WHERE owner_id = ?";
            $stmtUpdateOwner = $conn->prepare($updateOwnerStatus);
            $stmtUpdateOwner->bind_param("i", $owner_id);
            $stmtUpdateOwner->execute();

            // Redirect to the owner dashboard
            header("Location: owner_dashboard.php");
            exit();
        } else {
            // Email not found in owner_email table
            echo "<script>alert('Email does not exist!'); window.location.href='login.html';</script>";
        }
    } else {
        // Query to check the user credentials from the Customer table
        $stmt = $conn->prepare("SELECT * FROM Customer WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Verify password using password_verify()
            if (password_verify($password, $row['password'])) {
                // Check if the user is already logged in
                if ($row['is_logged_in'] == 1) {
                    // If the user is already logged in, prevent logging in again
                    echo "<script>alert('You are already logged in!'); window.location.href='home.html';</script>";
                    exit();
                }

                // Set user as logged in
                $updateLoginStatus = "UPDATE Customer SET is_logged_in = 1 WHERE id_customer = ?";
                $stmtUpdate = $conn->prepare($updateLoginStatus);
                $stmtUpdate->bind_param("i", $row['id_customer']);
                $stmtUpdate->execute();

                // Store customer ID in session
                $_SESSION['customer_id'] = $row['id_customer']; // Store id_customer for the customer

                // Redirect to home page after successful login
                header("Location: home.html");
                exit();
            } else {
                // Incorrect password
                echo "<script>alert('Incorrect password!'); window.location.href='login.html';</script>";
            }
        } else {
            // Email not found
            echo "<script>alert('Email does not exist!'); window.location.href='login.html';</script>";
        }
    }
}

// Close the database connection
$conn->close();
?>
