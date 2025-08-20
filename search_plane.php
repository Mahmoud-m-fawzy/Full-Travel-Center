<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'my_project');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Flight booking logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_flight'])) {
    $customerId = $_SESSION['customer_id'] ?? null; // Get customer ID from session

    if ($customerId) {
        $planeName = $_POST['plane_name'];
        $flightFrom = $_POST['flight_from'];
        $flightTo = $_POST['flight_to'];
        $departureDate = $_POST['departure_date'];
        $price = $_POST['price'];  // Get the price from the form
        $flightReturnDate = $_POST['flight_return_date'] ?? null;  // Get the return date from the form

        // Insert the flight booking into the database, including the return day
        $sql = "INSERT INTO plane_booking (customer_id, plane_name, flight_from, flight_to, flight_departure_date, flight_return_date, price) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssi", $customerId, $planeName, $flightFrom, $flightTo, $departureDate, $flightReturnDate, $price);

        if ($stmt->execute()) {
            echo "<script>alert('Flight booked successfully!'); window.location.href='Transportation.html';</script>";
        } else {
            echo "<script>alert('Failed to book the flight.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('You must be logged in to book a flight.'); window.location.href='login.html';</script>";
    }
}

// Get POST data for flight search
$from = $_POST['from'] ?? '';
$to = $_POST['to'] ?? '';
$depart = $_POST['depart'] ?? '';

// Query to search for flights based on input
$sql = "SELECT p.id_plane, p.name_of_plane, p.plane_day, p.plane_hours, p.availability, l.`From`, l.`To`, p.price_of_plane, p.return_day, p.image_url
        FROM Plane p
        JOIN Plane_Location l ON p.id_plane = l.id_plane
        WHERE l.`From` = ? AND l.`To` = ? AND p.plane_day >= ?";


$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $from, $to, $depart);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "
        <div class='result-item'>
            <img src='" . htmlspecialchars($row['image_url'] ?? 'default_image.jpg') . "' alt='Plane Image'>
            <div class='details'>
                <h3>" . htmlspecialchars($row['name_of_plane']) . "</h3>
                <p><strong>Day:</strong> " . htmlspecialchars($row['plane_day']) . "</p>
                <p><strong>Hours:</strong> " . htmlspecialchars($row['plane_hours']) . "</p>
                <p><strong>From:</strong> " . htmlspecialchars($row['From']) . " <strong>To:</strong> " . htmlspecialchars($row['To']) . "</p>
                <p><strong>Price:</strong> EGP " . number_format($row['price_of_plane'], 2) . "</p>
                <p><strong>Return Day:</strong> " . htmlspecialchars($row['return_day'] ?? 'Not Available') . "</p>
            </div>
            <form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>
                <input type='hidden' name='plane_name' value='" . htmlspecialchars($row['name_of_plane']) . "'>
                <input type='hidden' name='flight_from' value='" . htmlspecialchars($row['From']) . "'>
                <input type='hidden' name='flight_to' value='" . htmlspecialchars($row['To']) . "'>
                <input type='hidden' name='departure_date' value='" . htmlspecialchars($row['plane_day']) . "'>
                <input type='hidden' name='price' value='" . $row['price_of_plane'] . "'>
                <input type='hidden' name='flight_return_date' value='" . htmlspecialchars($row['return_day']) . "'>
                <button type='submit' name='book_flight' class='book-button'>Book Flight</button>
            </form>
        </div>";
    }
} else {
    echo "<p>No results found.</p>";
}

$conn->close();
?>
