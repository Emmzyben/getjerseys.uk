<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle cancellation
if (isset($_GET['status']) && $_GET['status'] === 'cancelled') {
    header('Location: checkout.php');
    exit();
}

// Get transaction details
$transactionId = $_GET['transaction_id'] ?? null;
$txRef = $_GET['tx_ref'] ?? null;
$status = $_GET['status'] ?? null;

// Validate necessary parameters
if (!$transactionId || !$txRef || $status !== 'successful') {
    die('Invalid transaction data or transaction was not successful.');
}

// Decode customer data if passed
$customerData = [];
if (isset($_GET['data'])) {
    $decodedData = base64_decode($_GET['data']);
    $customerData = json_decode($decodedData, true);
    if (!is_array($customerData)) {
        die('Invalid customer data.');
    }
}

// Get Flutterwave secret key from DB
$query = "SELECT secret FROM admins LIMIT 1";
$result = $conn->query($query);
if (!$result || $result->rowCount() === 0) {
    die('Secret key not found.');
}
$secretKey = $result->fetch(PDO::FETCH_ASSOC)['secret'];

// Verify transaction via Flutterwave API
$verifyUrl = "https://api.flutterwave.com/v3/transactions/{$transactionId}/verify";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verifyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $secretKey",
    "Content-Type: application/json",
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $response) {
    $result = json_decode($response, true);

    if ($result['status'] === 'success' && $result['data']['status'] === 'successful') {
        $amount = $result['data']['amount']; // in NGN

        try {
            if (!isset($_SESSION['cartItems']) || empty($_SESSION['cartItems'])) {
                throw new Exception("No cart items found in session.");
            }

            $cartItems = $_SESSION['cartItems'];

            // Create order in DB
            $orderId = createOrder($conn, $customerData, $cartItems, $amount);

            if ($orderId) {
                $_SESSION['cart'] = [];
                $_SESSION['cartItems'] = [];
                $_SESSION['order_id'] = $orderId;

                header('Location: order-confirmation.php?order_id=' . $orderId);
                exit;
            } else {
                throw new Exception("Order creation failed.");
            }
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    } else {
        die('Transaction not successful or failed to verify.');
    }
} else {
    die('Flutterwave verification failed.');
}
