<?php
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

// Check if the user is logged in
if (!isset($_SESSION['customer_id'])) {
    echo "<script>alert('You must be logged in to make a booking.'); window.location.href='login.html';</script>";
    exit();
}

// Retrieve the customer ID from session
$customerId = $_SESSION['customer_id'];

// Assuming you have form fields for selecting the bookings (hotel, plane, transportation, attraction)
$hotelBookingId = $_POST['hotel_booking_id'];  // Hotel booking ID from form
$planeBookingId = $_POST['plane_booking_id'];  // Plane booking ID from form
$transportationBookingId = $_POST['transportation_booking_id'];  // Transportation booking ID from form
$attractionBookingId = $_POST['attractions_booking_id'];  // Attraction booking ID from form

// Insert query for bookings table
$insertQuery = "
    INSERT INTO bookings (customer_id, hotel_booking_id, plane_booking_id, transportation_booking_id, attractions_booking_id)
    VALUES (?, ?, ?, ?, ?)";

// Prepare the statement
$stmt = $conn->prepare($insertQuery);

// Bind parameters (i - integer, s - string)
$stmt->bind_param("iiiii", $customerId, $hotelBookingId, $planeBookingId, $transportationBookingId, $attractionBookingId);

// Execute the query
if ($stmt->execute()) {
    echo "<script>alert('Booking added successfully!'); window.location.href='payment.php';</script>";
} else {
    echo "<script>alert('Failed to add booking. Please try again.'); window.location.href='payment.php';</script>";
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
