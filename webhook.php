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
    // Generate Expiring Link
    $expires = time() + (24 * 60 * 60); // 24 hours from now
    $signature = hash_hmac('sha256', $toEmail . $expires, RAZORPAY_WEBHOOK_SECRET);
    // Construct the full URL - assuming current host. In production, hardcode the domain if needed.
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'yourdomain.com'; // Fallback
    $downloadLink = "$protocol://$host/download.php?email=" . urlencode($toEmail) . "&expires=$expires&signature=$signature";

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
                <p><strong>Hi there,</strong></p>
                <p>Thank you for your purchase! We are thrilled to have you onboard.</p>
                <p>You have successfully unlocked access to the <strong>Reddit to Riches</strong> system. Here are your exclusive resources:</p>
                
                <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>

                <h3 style='color: #333;'>1. Your E-Book</h3>
                <p>Click the button below to download your copy. Please save it to your device immediately.</p>
                <p><em><strong>Note:</strong> For security, this personal link expires in <strong>24 hours</strong>.</em></p>
                <p style='text-align: center;'><a href='" . $downloadLink . "' class='button'>Download E-Book Now</a></p>
                
                <h3 style='color: #333; margin-top: 30px;'>2. The VIP Community</h3>
                <p>Don't forget to join our exclusive Telegram group to network with other high-achievers.</p>
                <p style='text-align: center;'><a href='" . TELEGRAM_LINK . "' style='color: #2ecc71; font-weight: bold; font-size: 16px; text-decoration: none;'>👉 Click Here to Join the Telegram Group</a></p>
                
                <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                
                <p style='font-size: 14px; color: #777;'>If you have any issues accessing these files, please reply to this email closely describing the issue.</p>
                
                <p>To your success,<br><strong>Arpit Sharma</strong></p>
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