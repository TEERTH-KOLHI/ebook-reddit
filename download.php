<?php
require_once 'config.php';

// 1. Get Parameters
$email = $_GET['email'] ?? '';
$expires = $_GET['expires'] ?? 0;
$signature = $_GET['signature'] ?? '';

// 2. Basic Validation
if (empty($email) || empty($expires) || empty($signature)) {
    die('Invalid Link: Missing parameters.');
}

// 3. Time Validation
if (time() > $expires) {
    die('Link Expired: This link was only valid for 24 hours. Please contact support if you need a new one.');
}

// 4. Signature Validation
// We recreate the signature using the same secret and data
$expectedSignature = hash_hmac('sha256', $email . $expires, RAZORPAY_WEBHOOK_SECRET);

if (!hash_equals($expectedSignature, $signature)) {
    die('Access Denied: Invalid signature.');
}

// 5. Serve File
$file = __DIR__ . '/protected/ebook.pdf';

if (file_exists($file)) {
    header('Content-Description: File Transfer');
    // Force download with original filename
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Reddit_to_Riches.pdf"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
} else {
    die('Error: File not found on server.');
}
?>