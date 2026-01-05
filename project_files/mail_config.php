<?php
/**
 * Email Configuration using PHPMailer with Gmail SMTP
 * 
 * SETUP INSTRUCTIONS:
 * 1. Go to your Google Account → Security → 2-Step Verification (enable it)
 * 2. Go to: https://myaccount.google.com/apppasswords
 * 3. Create an App Password (select "Mail" and "Other")
 * 4. Copy the 16-character password and paste it below
 * 5. Replace 'your-email@gmail.com' with your Gmail address
 */

// Include PHPMailer classes
require_once __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/src/SMTP.php';
require_once __DIR__ . '/vendor/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ============================================
// CONFIGURE THESE SETTINGS
// ============================================
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');      // Your Gmail address
define('MAIL_PASSWORD', 'your-app-password');          // 16-char App Password from Google
define('MAIL_FROM_NAME', 'Household Service Network');
// ============================================

/**
 * Send an email using PHPMailer
 * 
 * @param string $to Recipient email
 * @param string $toName Recipient name
 * @param string $subject Email subject
 * @param string $htmlBody HTML content of email
 * @return array ['success' => bool, 'message' => string]
 */
function sendEmail($to, $toName, $subject, $htmlBody) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = 'dip77797@gmail.com';
        $mail->Password   = 'mybsobrdrdkopgtl';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('dip77797@gmail.com', 'Household Service Network');
        $mail->addAddress($to, $toName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);
        
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Mailer Error: {$mail->ErrorInfo}"];
    }
}

/**
 * Send verification email to user
 * 
 * @param string $email User's email
 * @param string $name User's name
 * @param string $token Verification token
 * @return bool
 */
function sendVerificationEmail($email, $name, $token) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['REQUEST_URI']);
    $base_url = rtrim($protocol . '://' . $host . $path, '/');
    
    $verification_link = $base_url . '/verify_email.php?token=' . $token;
    
    $subject = "Verify Your Email - Household Service Network";
    $htmlBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background-color: #f9f9f9; }
            .btn { display: inline-block; background-color: #007bff; color: white; padding: 12px 30px; 
                   text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to Household Service Network!</h1>
            </div>
            <div class='content'>
                <p>Hello <strong>$name</strong>,</p>
                <p>Thank you for registering with us. Please verify your email address by clicking the button below:</p>
                <p style='text-align: center;'>
                    <a href='$verification_link' class='btn'>Verify Email Address</a>
                </p>
                <p>Or copy and paste this link in your browser:</p>
                <p style='word-break: break-all; background: #eee; padding: 10px;'>$verification_link</p>
                <p>If you did not create an account, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Household Service Network. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $result = sendEmail($email, $name, $subject, $htmlBody);
    return $result['success'];
}
?>
