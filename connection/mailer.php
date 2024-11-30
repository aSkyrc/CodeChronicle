<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the Composer autoloader (adjust the path if necessary)
require '../vendor/autoload.php';  // Adjust the path if needed

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

function sendOTPEmailUsingPHPMailer($recipientEmail, $otp) {
    global $mail;

    try {
        // Server settings
        $mail->isSMTP();                            // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';             // Set the SMTP server to Gmail
        $mail->SMTPAuth = true;                     // Enable SMTP authentication
        $mail->Username = 'acdm509@gmail.com';      // SMTP username (your Gmail address)
        $mail->Password = 'nwmj cwcd noiz vwsd';    // SMTP password (your Gmail App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Enable TLS encryption
        $mail->Port = 587;                          // TCP port to connect to

        // Recipients
        $mail->setFrom('no-reply@codechronicle.com', 'Code Chronicle');  // No-reply sender's email
        $mail->addAddress($recipientEmail);                         // Recipient's email

        // Set Reply-To header to the same no-reply address or leave it empty to avoid replies
        $mail->addReplyTo('no-reply@codechronicle.com', 'No Reply');

        // Content
        $mail->isHTML(true);  // Set email format to HTML
        $mail->Subject = 'Code Chronicle - OTP Verification';  // Subject
        $mail->Body    = "Your OTP for Code Chronicle registration is: <strong>$otp</strong>";  // HTML body
        $mail->AltBody = "Your OTP for Code Chronicle registration is: $otp";  // Plain text body (alternative)

        // Send email
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

    
?>
