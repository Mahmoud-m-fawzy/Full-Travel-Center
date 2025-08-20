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

// Transportation booking logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_transportation'])) {
    $customerId = $_SESSION['customer_id'] ?? null; // Get customer ID from session

    if ($customerId) {
        // Transportation booking form data
        $idOfCar = $_POST['id_of_car'];
        $typeOfCar = $_POST['type_of_car'];
        $pickupLocation = $_POST['pickup_location'];
        $pickupDate = $_POST['pickup_date'];
        $dropoffDate = $_POST['dropoff_date'];
        $driverName = $_POST['driver_name'] ?? 'Unknown'; // Handle missing driver name

        // Insert transportation booking into the database
        $sql = "INSERT INTO transportation_booking (id_of_car, type_of_car, id_customer, pickup_location, pickup_date, dropoff_date, name_of_driver) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isissss", $idOfCar, $typeOfCar, $customerId, $pickupLocation, $pickupDate, $dropoffDate, $driverName); 

        if ($stmt->execute()) {
            echo "<script>alert('Transportation booked successfully!'); window.location.href='Attractions.html';</script>";
        } else {
            echo "<script>alert('Failed to book transportation.');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('You must be logged in to book transportation.'); window.location.href='login.html';</script>";
    }
}

// Get search parameters from form for searching transportation
$pickup_location = $_POST['pickup-location'] ?? ''; // Default to empty if not set
$pickup_date = $_POST['pickup-date'] ?? ''; // Default to empty if not set
$dropoff_date = $_POST['dropoff-date'] ?? ''; // Default to empty if not set

// Query to search for transportation options based on pickup location and date
$sql = "SELECT 
            t.id_of_car, 
            t.type_of_car, 
            t.name_of_driver, 
            t.phone_num, 
            t.pickup_location, 
            t.pickup_date, 
            t.dropoff_date, 
            t.image_url, 
            tp.price_of_trans
        FROM transportation t
        LEFT JOIN transportation_price tp ON t.id_of_car = tp.id_of_car
        WHERE t.pickup_location LIKE ? AND t.pickup_date >= ? AND t.dropoff_date <= ?";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);
$pickup_location = "%$pickup_location%";
$stmt->bind_param("sss", $pickup_location, $pickup_date, $dropoff_date);
$stmt->execute();
$result = $stmt->get_result();

// Start generating the HTML output for transportation search results
$output = '<div class="results-container">';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $driverName = $row['name_of_driver'] ?? 'Unknown'; // Set a default value if driver name is NULL
        $output .= '
        <div class="result-item">
            <img src="' . htmlspecialchars($row['image_url'] ?? 'default_image.jpg') . '" alt="Car Image" class="transportation-image">
            <div class="details">
                <h3>' . htmlspecialchars($row['type_of_car']) . '</h3>
                <p>Driver: ' . htmlspecialchars($driverName) . '</p>
                <p>Phone: ' . htmlspecialchars($row['phone_num']) . '</p>
                <p>Pickup Location: ' . htmlspecialchars($row['pickup_location']) . '</p>
                <p>Pickup Date: ' . htmlspecialchars($row['pickup_date']) . '</p>
                <p>Drop-off Date: ' . htmlspecialchars($row['dropoff_date']) . '</p>
                <p>Price: EGP ' . number_format($row['price_of_trans'] ?? 0, 2) . '</p>
            </div>
            <form action="' . $_SERVER['PHP_SELF'] . '" method="POST">
                <input type="hidden" name="id_of_car" value="' . $row['id_of_car'] . '">
                <input type="hidden" name="type_of_car" value="' . htmlspecialchars($row['type_of_car']) . '">
                <input type="hidden" name="pickup_location" value="' . htmlspecialchars($row['pickup_location']) . '">
                <input type="hidden" name="pickup_date" value="' . htmlspecialchars($row['pickup_date']) . '">
                <input type="hidden" name="dropoff_date" value="' . htmlspecialchars($row['dropoff_date']) . '">
                <input type="hidden" name="driver_name" value="' . htmlspecialchars($driverName) . '"> <!-- Added driver name hidden field -->
                <button type="submit" name="book_transportation" class="book-button">Book Now</button>
            </form>
        </div>';
    }
} else {
    $output .= '<p>No transportation options found.</p>';
}

$output .= '</div>';

// Close the connection
$stmt->close();
$conn->close();

// Echo the output to be used in HTML
echo $output;
?>
