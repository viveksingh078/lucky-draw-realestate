<?php

namespace Botble\RealEstate\Helpers;

class QRCodeHelper
{
    /**
     * Generate UPI QR Code URL
     * 
     * @param string $upiId - Your UPI ID (e.g., yourname@paytm)
     * @param float $amount - Payment amount
     * @param string $name - Payee name
     * @param string $note - Payment note
     * @return string - QR Code image URL
     */
    public static function generateUpiQRCode($upiId, $amount, $name = 'AADS Property Portal', $note = 'Membership Payment')
    {
        // Create UPI payment string
        $upiString = "upi://pay?pa={$upiId}&pn=" . urlencode($name) . "&am={$amount}&cu=INR&tn=" . urlencode($note);
        
        // Generate QR code using QR Server API (free and reliable)
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($upiString);
        
        return $qrCodeUrl;
    }

    /**
     * Generate and save QR code image
     * 
     * @param string $upiId
     * @param float $amount
     * @param string $name
     * @param string $note
     * @return string|false - Saved file path or false on failure
     */
    public static function generateAndSaveQRCode($upiId, $amount, $name = 'AADS Property Portal', $note = 'Membership Payment')
    {
        try {
            $qrCodeUrl = self::generateUpiQRCode($upiId, $amount, $name, $note);
            
            // Download QR code image
            $imageData = @file_get_contents($qrCodeUrl);
            
            if ($imageData === false) {
                return false;
            }
            
            // Create directory if not exists
            $directory = public_path('storage/qr-codes');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Generate unique filename
            $filename = 'qr_' . time() . '_' . md5($upiId . $amount) . '.png';
            $filepath = $directory . '/' . $filename;
            
            // Save image
            file_put_contents($filepath, $imageData);
            
            return 'storage/qr-codes/' . $filename;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get QR code data URL (base64 encoded)
     * 
     * @param string $upiId
     * @param float $amount
     * @param string $name
     * @param string $note
     * @return string
     */
    public static function generateQRCodeDataUrl($upiId, $amount, $name = 'AADS Property Portal', $note = 'Membership Payment')
    {
        $qrCodeUrl = self::generateUpiQRCode($upiId, $amount, $name, $note);
        
        try {
            $imageData = @file_get_contents($qrCodeUrl);
            if ($imageData !== false) {
                return 'data:image/png;base64,' . base64_encode($imageData);
            }
        } catch (\Exception $e) {
            // Return fallback
        }
        
        return $qrCodeUrl;
    }
}
