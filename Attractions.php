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

// Attraction booking logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_attraction'])) {
    $customerId = $_SESSION['customer_id'] ?? null; // Check if customer ID is set in session

    if ($customerId) {
        // Attraction booking form data
        $attractionId = $_POST['attraction_id'];
        $city = $_POST['city'];
        $country = $_POST['country'];
        $price = $_POST['price'];
        $visitors = $_POST['visitors'] ?? 0; // Ensure visitors has a default value of 0 if not provided

        // Insert attraction booking into the database
        $sql = "INSERT INTO attractions_booking (id_customer, id, location_city, location_country, price, visitors)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssi", $customerId, $attractionId, $city, $country, $price, $visitors);

        if ($stmt->execute()) {
            echo "<script>alert('Attraction booked successfully!'); window.location.href='payment.php';</script>";
        } else {
            echo "<script>alert('Failed to book the attraction.');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('You must be logged in to book an attraction.'); window.location.href='login.html';</script>";
    }
    exit();
}

// Get inputs for searching attractions
$whereTo = $_POST['where_to'] ?? ''; // Default to empty if not set
$visitors = $_POST['visitors'] ?? 1; // Default to 1 if not set

// Query to search for attractions based on city
$sql = "SELECT 
            id_places, 
            name, 
            image_url, 
            city, 
            country, 
            price, 
            hours 
        FROM attraction 
        WHERE city LIKE ?";
$stmt = $conn->prepare($sql);
$param = "%$whereTo%";
$stmt->bind_param("s", $param);
$stmt->execute();
$result = $stmt->get_result();

// Start generating the HTML output for attraction search results
$output = '<div class="results-container">';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $output .= '
        <div class="result-item">
            <img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['name']) . '" class="result-image">
            <div class="details">
                <h3>' . htmlspecialchars($row['name']) . '</h3>
                <p>City: ' . htmlspecialchars($row['city']) . '</p>
                <p>Country: ' . htmlspecialchars($row['country']) . '</p>
                <p>Price: ' . htmlspecialchars($row['price']) . ' USD</p>
                <p>Operating Hours: ' . htmlspecialchars($row['hours']) . '</p>
            </div>
            <form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="POST">
                <input type="hidden" name="attraction_id" value="' . htmlspecialchars($row['id_places']) . '">
                <input type="hidden" name="city" value="' . htmlspecialchars($row['city']) . '">
                <input type="hidden" name="country" value="' . htmlspecialchars($row['country']) . '">
                <input type="hidden" name="price" value="' . htmlspecialchars($row['price']) . '">
                <input type="hidden" name="visitors" value="' . htmlspecialchars($visitors) . '">
                <button type="submit" name="book_attraction" class="book-button">Book Attraction</button>
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
