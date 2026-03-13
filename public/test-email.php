<?php
/**
 * Test Email Sending
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Email Functionality</h2>";

$to = "viveksingh45.sipl@gmail.com"; // Change to your email
$subject = "Test Email from AADS Property Portal";
$message = "This is a test email to verify email functionality is working. If you receive this, email sending is configured correctly!";

$headers = "From: AADS Property Portal <noreply@sspl20.com>\r\n";
$headers .= "Reply-To: noreply@sspl20.com\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

echo "<p>Sending email to: <strong>$to</strong></p>";

if (mail($to, $subject, $message, $headers)) {
    echo "<h3 style='color: green;'>✅ Email sent successfully!</h3>";
    echo "<p>Check your inbox and spam folder.</p>";
} else {
    echo "<h3 style='color: red;'>❌ Email sending failed!</h3>";
    echo "<p>Server might not have mail() function configured.</p>";
}

echo "<hr>";
echo "<p><strong>Server Info:</strong></p>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "mail() function: " . (function_exists('mail') ? 'Available' : 'NOT Available') . "\n";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "</pre>";
?>
