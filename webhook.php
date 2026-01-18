<?php
require_once 'config.php';

// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit('Forbidden');
}

$webhookBody = file_get_contents('php://input');
$webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

// 1. Verify Signature
if (empty($webhookSignature)) {
    http_response_code(400);
    exit('Signature missing');
}

$generatedSignature = hash_hmac('sha256', $webhookBody, RAZORPAY_WEBHOOK_SECRET);

if ($generatedSignature !== $webhookSignature) {
    http_response_code(400);
    exit('Invalid signature');
}

// 2. Process Event
$data = json_decode($webhookBody, true);
$event = $data['event'] ?? '';

if ($event === 'payment.captured') {
    $payment = $data['payload']['payment']['entity'];
    $email = $payment['email'];
    $amount = $payment['amount'] / 100; // Amount in rupees

    // Send delivery email
    $deliverySent = sendDeliveryEmail($email);

    if ($deliverySent) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Email sent']);
    } else {
        // Log error (in a real app)
        http_response_code(200); // Still return 200 to Razorpay so they don't retry indefinitely
        echo json_encode(['status' => 'warning', 'message' => 'Email failed to send']);
    }
} else {
    // We only care about payment.captured, return 200 to ignore others
    http_response_code(200);
}

/**
 * Sends the access email using standard PHP mail()
 */
function sendDeliveryEmail($toEmail)
{
    // --- CONFIGURATION ---
    // Links are now loaded from config.php
    $fromName = "Reddit to Riches";
    $fromEmail = "info@arpitsharmawriting.com"; // Verified sender matching your domain
    // ---------------------

    $subject = "Access Granted: Reddit to Riches E-Book + Community";

    // HTML Message
    $message = "
    <html>
    <head>
        <title>Your Access Details</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
            .header { background-color: #2ecc71; color: white; padding: 15px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { padding: 20px; }
            .button { display: inline-block; padding: 12px 24px; color: white; background-color: #FF4500; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 10px 0; }
            .footer { font-size: 12px; color: #666; text-align: center; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Thank You for Your Order!</h2>
            </div>
            <div class='content'>
                <p>Hi there,</p>
                <p>Congratulations on taking the first step! We've successfully received your payment.</p>
                
                <h3>Files & Links:</h3>
                
                <p><strong>1. Download Your E-Book:</strong></p>
                <p><a href='" . EBOOK_LINK . "' class='button'>Download Reddit to Riches PDF</a></p>
                
                <p><strong>2. Join the VIP Community:</strong></p>
                <p><a href='" . TELEGRAM_LINK . "' style='color: #2ecc71; font-weight: bold;'>Click here to join the Telegram Group</a></p>
                
                <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                
                <p>If you have any trouble accessing the files, just reply to this email.</p>
                
                <p>Cheers,<br>Arpit Sharma</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date("Y") . " Reddit to Riches. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . $fromName . " <" . $fromEmail . ">" . "\r\n";
    $headers .= "Reply-To: " . $fromEmail . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    return mail($toEmail, $subject, $message, $headers);
}
?>