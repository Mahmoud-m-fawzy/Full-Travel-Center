<?php
// Fetch bookings for the owner
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_project";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
$sql = "SELECT * FROM bookings";  // Query to fetch all bookings
$result = $conn->query($sql);

// Delete booking functionality
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Prepare and execute the delete query
    $delete_sql = "DELETE FROM bookings WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to the dashboard after deletion
    header("Location: owner_dashboard.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard</title>
    <link rel="stylesheet" href="owner_dashboard.css"> <!-- Link to the separate CSS file -->
</head>
<body>
    <header>
        <div class="logo">TRAVEL<span>Center</span></div>
        <ul class="nav-links">
            <li><a href="home.html">Home</a></li>
            <li><a href="Hotels.html">Hotels</a></li>
            <li><a href="Plane.html">Plane</a></li>
            <li><a href="Transportation.html">Transportation</a></li>
            <li><a href="Attractions.html">Attractions</a></li>
            <li><a href="aboutus.html">About Us</a></li>
            <li><a href="owner_dashboard.php" class="active">Dashboard</a></li>
            <form action="logout.php" method="POST">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </ul>
    </header>

    <main>
        <div class="dashboard-container">
            <h1>Welcome, Owner!</h1>
            <p>You are now logged in as an owner.</p>
    
            <!-- Dashboard Info Section -->
            <div class="owner-info">
                <h2>Owner Dashboard</h2>
                <p>Here you can manage your properties, view bookings, or perform other administrative tasks.</p>
    
                <div class="owner-actions">
                    <h2>Customer Bookings</h2>
                    <div class="results-container">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer ID</th>
                                    <th>Hotel Booking ID</th>
                                    <th>Plane Booking ID</th>
                                    <th>Transportation Booking ID</th>
                                    <th>Attractions Booking ID</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="bookingData">
                                <?php
                                if ($result->num_rows > 0) {
                                    // Output data of each row
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                                <td>" . htmlspecialchars($row['id'] ?? '') . "</td>
                                                <td>" . htmlspecialchars($row['customer_id'] ?? '') . "</td>
                                                <td>" . htmlspecialchars($row['hotel_booking_id'] ?? '') . "</td>
                                                <td>" . htmlspecialchars($row['plane_booking_id'] ?? '') . "</td>
                                                <td>" . htmlspecialchars($row['transportation_booking_id'] ?? '') . "</td>
                                                <td>" . htmlspecialchars($row['attractions_booking_id'] ?? '') . "</td>
                                                <td>
                                                    <a href='?delete_id=" . $row['id'] . "' onclick='return confirm(\"Are you sure you want to delete this booking?\")'>Delete</a>
                                                </td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7'>No bookings found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

<?php
// Close the connection
$conn->close();
?>
