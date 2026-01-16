<?php
require_once 'config.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['amount'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Amount is required']);
    exit;
}

$amount = $data['amount'];
$currency = isset($data['currency']) ? $data['currency'] : 'INR';

// Razorpay API URL
$url = 'https://api.razorpay.com/v1/orders';

// Create order data
$orderData = [
    'amount' => $amount,
    'currency' => $currency,
    'receipt' => 'receipt_' . time(),
    'payment_capture' => 1 // Auto capture
];

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($orderData));

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'Curl error: ' . curl_error($ch)]);
} else {
    if ($httpCode === 200) {
        echo $response;
    } else {
        http_response_code($httpCode);
        echo $response;
    }
}

curl_close($ch);
?>