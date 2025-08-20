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

// Hotel booking logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_hotel'])) {
    $customerId = $_SESSION['customer_id'] ?? null; // Get customer ID from session

    if ($customerId) {
        // Hotel booking form data
        $hotelName = $_POST['hotel_name'];
        $roomType = $_POST['room_type'];
        $price = $_POST['price'];
        $city = $_POST['city'];
        $travelers = $_POST['travelers'] ?? 0; // Ensure travelers has a default value of 0 if not provided

        // Insert hotel booking into the database
        $sql = "INSERT INTO hotel_booking (customer_id, hotel_name, room_type, price, city, travelers)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssi", $customerId, $hotelName, $roomType, $price, $city, $travelers);

        if ($stmt->execute()) {
            echo "<script>alert('Hotel booked successfully!'); window.location.href='plane.html';</script>";
        } else {
            echo "<script>alert('Failed to book the hotel.');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('You must be logged in to book a hotel.'); window.location.href='login.html';</script>";
    }
}


// Get inputs from form for searching hotels
$city = $_POST['city'] ?? ''; // Default to empty if not set
$roomType = $_POST['roomType'] ?? ''; // Default to empty if not set
$travelers = $_POST['travelers'] ?? 1; // Ensure travelers has a default value of 1 if not provided

// Query to search for hotels based on city and room type
$sql = "SELECT 
            hotels.name_of_hotel, 
            hotels.city, 
            hotels.description, 
            hotels.image_url, 
            room.type_of_room, 
            room_price.price_room, 
            hotels.rating, 
            hotels.id_hotel
        FROM hotels
        JOIN room ON hotels.id_hotel = room.id_hotel
        JOIN room_price ON room.id_room = room_price.id_room
        WHERE hotels.city = ? AND room.type_of_room = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $city, $roomType);
$stmt->execute();
$result = $stmt->get_result();


// Start generating the HTML output for hotel search results
$output = '<div class="results-container">';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $output .= '
        <div class="result-item">
            <img src="' . $row['image_url'] . '" alt="' . $row['name_of_hotel'] . '" class="hotel-image">
            <div class="details">
                <h3>' . $row['name_of_hotel'] . '</h3>
                <p>' . $row['description'] . '</p>
                <p class="city">' . $row['city'] . '</p>
                <p class="rating">⭐ ' . $row['rating'] . '/5</p>
                <p class="price">EGP ' . $row['price_room'] . '</p>
            </div>
            <form action="' . $_SERVER['PHP_SELF'] . '" method="POST">
                <input type="hidden" name="hotel_name" value="' . htmlspecialchars($row['name_of_hotel']) . '">
                <input type="hidden" name="hotel_id" value="' . $row['id_hotel'] . '">
                <input type="hidden" name="room_type" value="' . htmlspecialchars($row['type_of_room']) . '">
                <input type="hidden" name="price" value="' . $row['price_room'] . '">
                <input type="hidden" name="city" value="' . htmlspecialchars($row['city']) . '">
                <input type="hidden" name="travelers" value="' . $travelers . '"> <!-- Include travelers value -->
                <button type="submit" name="book_hotel" class="book-button">Book Hotel</button>
            </form>
        </div>';
    }
} else {
    $output .= '<p>No results found for your search.</p>';
}

$output .= '</div>';

// Close the connection
$stmt->close();
$conn->close();

// Echo the output to be used in HTML
echo $output;
?>
