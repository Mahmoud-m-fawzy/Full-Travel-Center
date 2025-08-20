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

// Check if the user is logged in (you might have session management for this)
if (!isset($_SESSION['customer_id'])) {
    echo "<script>alert('You must be logged in to view your bookings.'); window.location.href='inserting.php';</script>";
    exit();
}

// Retrieve the customer's bookings
$customerId = $_SESSION['customer_id'];

// Query to get hotel bookings
$hotelBookingsQuery = "SELECT hb.id, hb.hotel_name, hb.room_type, hb.price, hb.city, hb.travelers
                       FROM hotel_booking hb
                       WHERE hb.customer_id = ?";
$stmt = $conn->prepare($hotelBookingsQuery);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$hotelResult = $stmt->get_result();

// Query to get plane bookings
$planeBookingsQuery = "SELECT pb.id, pb.plane_name, pb.flight_from, pb.flight_to, pb.price, pb.flight_departure_date, pb.flight_return_date
                       FROM plane_booking pb
                       WHERE pb.customer_id = ?";
$stmt = $conn->prepare($planeBookingsQuery);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$planeResult = $stmt->get_result();

// Query to get transportation bookings
$transportationBookingsQuery = "SELECT tb.id_transport_booking, tb.type_of_car, tb.pickup_location, tb.pickup_date, tb.dropoff_date, tb.name_of_driver, tp.price_of_trans
                                FROM transportation_booking tb
                                LEFT JOIN transportation_price tp ON tb.id_of_car = tp.id_of_car
                                WHERE tb.id_customer = ?";
$stmt = $conn->prepare($transportationBookingsQuery);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$transportationResult = $stmt->get_result();

// Query to get attractions bookings, including location information
$attractionsBookingsQuery = "
    SELECT ab.id, ab.visitors, ab.price, a.name, a.city as location_city, a.country as location_country
    FROM attractions_booking ab
    LEFT JOIN attraction a ON ab.location_city = a.city AND ab.location_country = a.country
    WHERE ab.id_customer = ?
";

// Prepare and execute the query
$stmt = $conn->prepare($attractionsBookingsQuery);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$attractionsResult = $stmt->get_result();


// Handle deletion of a booking
if (isset($_GET['delete_booking'])) {
    $bookingId = $_GET['delete_booking'];
    $type = $_GET['type'];

    if ($type == 'hotel') {
        $deleteQuery = "DELETE FROM hotel_booking WHERE id = ? AND customer_id = ?";
    } elseif ($type == 'plane') {
        $deleteQuery = "DELETE FROM plane_booking WHERE id = ? AND customer_id = ?";
    } elseif ($type == 'transportation') {
        $deleteQuery = "DELETE FROM transportation_booking WHERE id_transport_booking = ? AND id_customer = ?";
    } else {
        $deleteQuery = "DELETE FROM attractions_booking WHERE id = ? AND id_customer = ?";
    }

    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("ii", $bookingId, $customerId);
    if ($stmt->execute()) {
        echo "<script>alert('Booking deleted successfully.'); window.location.href='payment.php';</script>";
    } else {
        echo "<script>alert('Failed to delete booking.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Page</title>
    <link rel="stylesheet" href="Payment.css">
</head>
<body>
    <header>
        <div>
            <div class="logo">TRAVEL<span>Center</span></div>
            <ul class="nav-links">
                <li><a href="home.html">Home</a></li>
                <li><a href="Hotels.html">Hotels</a></li>
                <li><a href="Plane.html">Plane</a></li>
                <li><a href="Transportation.html">Transportation</a></li>
                <li><a href="Attractions.html">Attractions</a></li>
                <li><a href="aboutus.html">About Us</a></li>
                <li><a href="payment.php" class="active">Payments</a></li>
            </ul>
        </div>
    </header>

    <main>
        <!-- Hotel Bookings Section -->
        <div class="booking-section">
            <h2>Hotel Bookings</h2>
            <?php if ($hotelResult->num_rows > 0): ?>
                <ul>
                    <?php while ($row = $hotelResult->fetch_assoc()): ?>
                        <?php
                        // Calculate the total price based on the number of travelers
                        $totalPrice = $row['price'] * $row['travelers'];
                        ?>
                        <li>
                            <p><strong>Hotel Name:</strong> <?php echo htmlspecialchars($row['hotel_name']); ?></p>
                            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($row['room_type']); ?></p>
                            <p><strong>Price per Traveler:</strong> EGP <?php echo htmlspecialchars($row['price']); ?></p>
                            <p><strong>City:</strong> <?php echo htmlspecialchars($row['city']); ?></p>
                            <p><strong>Travelers:</strong> <?php echo htmlspecialchars($row['travelers']); ?></p>
                            <p><strong>Total Price:</strong> EGP <?php echo number_format($totalPrice, 2); ?></p>
                            <a href="payment.php?delete_booking=<?php echo $row['id']; ?>&type=hotel" class="delete-button">Delete Booking</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>You have no hotel bookings.</p>
            <?php endif; ?>
        </div>

        <!-- Plane Bookings Section -->
        <div class="booking-section">
            <h2>Plane Bookings</h2>
            <?php if ($planeResult->num_rows > 0): ?>
                <ul>
                    <?php while ($row = $planeResult->fetch_assoc()): ?>
                        <li>
                            <p><strong>Plane Name:</strong> <?php echo htmlspecialchars($row['plane_name']); ?></p>
                            <p><strong>From:</strong> <?php echo htmlspecialchars($row['flight_from']); ?></p>
                            <p><strong>To:</strong> <?php echo htmlspecialchars($row['flight_to']); ?></p>
                            <p><strong>Price:</strong> EGP <?php echo htmlspecialchars($row['price']); ?></p>
                            <p><strong>Departure Date:</strong> <?php echo htmlspecialchars($row['flight_departure_date']); ?></p>
                            <p><strong>Return Date:</strong> <?php echo htmlspecialchars($row['flight_return_date'] ?? 'Not Available'); ?></p>
                            <a href="payment.php?delete_booking=<?php echo $row['id']; ?>&type=plane" class="delete-button">Delete Booking</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>You have no plane bookings.</p>
            <?php endif; ?>
        </div>

        <!-- Transportation Bookings Section -->
        <div class="booking-section">
            <h2>Transportation Bookings</h2>
            <?php if ($transportationResult->num_rows > 0): ?>
                <ul>
                    <?php while ($row = $transportationResult->fetch_assoc()): ?>
                        <li>
                            <p><strong>Car Type:</strong> <?php echo htmlspecialchars($row['type_of_car']); ?></p>
                            <p><strong>Pickup Location:</strong> <?php echo htmlspecialchars($row['pickup_location']); ?></p>
                            <p><strong>Pickup Date:</strong> <?php echo htmlspecialchars($row['pickup_date']); ?></p>
                            <p><strong>Drop-off Date:</strong> <?php echo htmlspecialchars($row['dropoff_date']); ?></p>
                            <p><strong>Driver Name:</strong> <?php echo htmlspecialchars($row['name_of_driver']); ?></p>
                            <p><strong>Price:</strong> EGP <?php echo htmlspecialchars($row['price_of_trans']); ?></p>
                            <a href="payment.php?delete_booking=<?php echo $row['id_transport_booking']; ?>&type=transportation" class="delete-button">Delete Booking</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>You have no transportation bookings.</p>
            <?php endif; ?>
        </div>

        <!-- Attractions Bookings Section -->
        <div class="booking-section">
            <h2>Attractions Bookings</h2>
            <?php if ($attractionsResult->num_rows > 0): ?>
                <ul>
                    <?php while ($row = $attractionsResult->fetch_assoc()): ?>
                        <?php
                        // Calculate the total price based on the number of visitors
                        $totalPrice = $row['price'] * $row['visitors'];
                        ?>
                        <li>
                            <p><strong>Attraction Name:</strong> <?php echo htmlspecialchars($row['name']); ?></p>
                            <p><strong>Location City:</strong> <?php echo htmlspecialchars($row['location_city']); ?></p>
                            <p><strong>Location Country:</strong> <?php echo htmlspecialchars($row['location_country']); ?></p>
                            <p><strong>Price per Visitor:</strong> EGP <?php echo htmlspecialchars($row['price']); ?></p>
                            <p><strong>Visitors:</strong> <?php echo htmlspecialchars($row['visitors']); ?></p>
                            <p><strong>Total Price:</strong> EGP <?php echo number_format($totalPrice, 2); ?></p>
                            <a href="payment.php?delete_booking=<?php echo $row['id']; ?>&type=attraction" class="delete-button">Delete Booking</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>You have no attractions bookings.</p>
            <?php endif; ?>
        </div>
    </main>
    <div class="payment-container">
        <h1>Payment Portal</h1>
        <form id="payment-form" action="process_payment.php" method="POST">
            <label for="method">Payment Method:</label>
            <select id="method" name="method" onchange="togglePaymentMethod()" required>
                <option value="">Select a payment method</option>
                <option value="visa">Visa</option>
                <option value="instapay">InstaPay</option>
            </select>

            <!-- Visa Card Details -->
            <div id="visa-details" style="display: none;">
                <label for="card-number">Card Number:</label>
                <input type="text" id="card-number" name="card_number" placeholder="Enter your card number">
                
                <label for="card-expiry">Expiry Date:</label>
                <input type="month" id="card-expiry" name="card_expiry">

                <label for="card-cvv">CVV:</label>
                <input type="text" id="card-cvv" name="card_cvv" placeholder="Enter CVV">
            </div>

            <!-- InstaPay QR Code -->
            <div id="instapay-details" style="display: none;">
                <h2>Scan the QR Code to Pay</h2>
                <img src="WhatsApp Image 2025-01-27 at 07.40.36_a33a366c.jpg" alt="InstaPay QR Code" id="instapay-qr">
            </div>

            <button type="submit">Pay</button>
        </form>
    </div>

    <script>
        function togglePaymentMethod() {
            const method = document.getElementById('method').value;
            const visaDetails = document.getElementById('visa-details');
            const instapayDetails = document.getElementById('instapay-details');

            // Toggle display based on selection
            if (method === 'visa') {
                visaDetails.style.display = 'block';
                instapayDetails.style.display = 'none';
            } else if (method === 'instapay') {
                instapayDetails.style.display = 'block';
                visaDetails.style.display = 'none';
            } else {
                visaDetails.style.display = 'none';
                instapayDetails.style.display = 'none';
            }
        }
    </script>
</body>
</html>

</body>
</html>
