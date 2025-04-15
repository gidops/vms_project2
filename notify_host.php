<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guest = $_POST['guest'];
    $host_email = $_POST['host_email'];
    $host_name = $_POST['host_name'];

    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ugorjigideon2@gmail.com';
        $mail->Password = 'tzfitrlpqegfcmag';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        // Email content
        $mail->setFrom('yourgmail@gmail.com', 'AATC Visitor Management');
        $mail->addAddress($host_email, $host_name);

        $mail->isHTML(true);
        $mail->Subject = "Visitor Arrival Notification";
        $mail->Body = "<p>Hello <strong>$host_name</strong>,</p><p><strong>$guest</strong> has arrived to see you at the premises.</p><p>— AATC Visitor Management System</p>";

        $mail->send();
        echo "✅ Host has been notified!";
    } catch (Exception $e) {
        echo "❌ Mail error: {$mail->ErrorInfo}";
    }
}
?>
