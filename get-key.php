<?php
require_once 'config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Return the key_id
echo json_encode(['key' => RAZORPAY_KEY_ID]);
?>