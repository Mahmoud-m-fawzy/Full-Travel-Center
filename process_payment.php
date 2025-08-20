<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $method = $_POST['method'];

    if ($method === 'visa') {
        // Process Visa payment
        $cardNumber = $_POST['card_number'];
        $cardExpiry = $_POST['card_expiry'];
        $cardCVV = $_POST['card_cvv'];

        if (!empty($cardNumber) && !empty($cardExpiry) && !empty($cardCVV)) {
            // Add logic to process the Visa payment (e.g., via a payment gateway API)
            echo "Visa payment processed successfully.";
        } else {
            echo "Please fill in all Visa card details.";
        }
    } elseif ($method === 'instapay') {
        // InstaPay is handled via QR code
        echo "Please scan the QR code to complete your payment.";
    } else {
        echo "Invalid payment method.";
    }
} else {
    echo "Invalid request.";
}
?>
