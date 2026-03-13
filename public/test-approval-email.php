<?php
/**
 * Test Approval Email Directly
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Approval Email</h2>";

// Test data
$accountName = "Vivek Thakur";
$accountEmail = "viveksingh45.sipl@gmail.com";
$planName = "Gold";
$planPrice = "1999.00";
$planDuration = "12 Months";
$siteName = "AADS Property Portal";
$loginUrl = "https://sspl20.com/realestate/public/account/login";

$subject = "Account Approved - Welcome to " . $siteName;

$message = "
<html>
<head>
    <title>Account Approved</title>
</head>
<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
        <div style='background: #28a745; color: white; padding: 20px; text-align: center;'>
            <h1 style='margin: 0;'>Account Approved!</h1>
        </div>
        <div style='padding: 20px; background: #f9f9f9;'>
            <h2>Congratulations " . $accountName . "!</h2>
            <p>Your account has been approved and is now active.</p>
            
            <div style='background: #fff; border: 2px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 8px;'>
                <h3 style='color: #28a745; margin-top: 0;'>Your Membership Details</h3>
                <table style='width: 100%;'>
                    <tr><td><strong>Plan:</strong></td><td>" . $planName . "</td></tr>
                    <tr><td><strong>Price:</strong></td><td>Rs. " . $planPrice . "</td></tr>
                    <tr><td><strong>Duration:</strong></td><td>" . $planDuration . "</td></tr>
                    <tr><td><strong>Status:</strong></td><td><span style='color: #28a745; font-weight: bold;'>ACTIVE</span></td></tr>
                </table>
            </div>
            
            <p><strong>You can now:</strong></p>
            <ul>
                <li>Login to your account</li>
                <li>Post properties</li>
                <li>Access all membership features</li>
                <li>Manage your listings</li>
            </ul>
            
            <p style='text-align: center; margin: 30px 0;'>
                <a href='" . $loginUrl . "' style='background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Login Now</a>
            </p>
            
            <p>Thank you for choosing " . $siteName . "!</p>
            
            <p>Best Regards,<br><strong>" . $siteName . " Team</strong></p>
        </div>
    </div>
</body>
</html>
";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: " . $siteName . " <noreply@sspl20.com>\r\n";

echo "<p>Sending approval email to: <strong>$accountEmail</strong></p>";

if (mail($accountEmail, $subject, $message, $headers)) {
    echo "<h3 style='color: green;'>✅ Approval email sent successfully!</h3>";
    echo "<p>Check inbox: <strong>$accountEmail</strong></p>";
    echo "<p>Also check spam folder.</p>";
} else {
    echo "<h3 style='color: red;'>❌ Email sending failed!</h3>";
    echo "<p>There might be an issue with the mail server.</p>";
}

echo "<hr>";
echo "<h3>Email Preview:</h3>";
echo "<div style='border: 2px solid #ddd; padding: 10px;'>";
echo $message;
echo "</div>";
?>
