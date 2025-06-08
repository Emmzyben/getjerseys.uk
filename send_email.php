<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer (adjust path if needed)
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendEmail($fullName, $email, $message, $subject) {
    // Check if the email is provided
    if (empty($email)) {
        error_log('Email address is empty');
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.lytehosting.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'support@getjerseys.uk'; // Replace with your Hostinger email
        $mail->Password   = 'Admin2025@';        // Replace with your Hostinger email password
        $mail->SMTPSecure = 'ssl';                 // Use 'ssl' if you want port 465
        $mail->Port       = 465;                   // Use 465 for SSL

        // Recipients
        $mail->setFrom('support@getjerseys.uk', 'Getjerseys');
        $mail->addAddress($email, $fullName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Email sending failed: ' . $mail->ErrorInfo);
        return false;
    }
}
?>
