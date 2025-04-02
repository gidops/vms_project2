<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the PHP QR Code library
include('phpqrcode/qrlib.php');

// Load PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// Database connection
$conn = new mysqli("localhost", "root", "", "visitor");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'id' is set in the URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Ensure ID is an integer to prevent SQL injection

    // Create the 'qr_codes' directory if it doesn't exist
    if (!file_exists('qr_codes')) {
        mkdir('qr_codes', 0777, true);
    }

    // Generate a unique QR Code
    $qr_code = "QR-" . uniqid();
    $qr_code_path = 'qr_codes/' . $qr_code . '.png';  // Use the correct variable for file name

    // Generate and save the QR code image
    QRcode::png($qr_code, $qr_code_path);  // Create the QR code and save it to the file

    // Update visitor status and assign QR code
    $stmt = $conn->prepare("UPDATE visitors SET status='approved', qr_code=? WHERE id=?");
    $stmt->bind_param("si", $qr_code, $id);
    $stmt->execute();

    // Retrieve visitor email
    $stmt = $conn->prepare("SELECT email FROM visitors WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $visitor = $result->fetch_assoc();
        $visitor_email = $visitor['email'];

        // Send email with PHPMailer
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 2; // Enable detailed debugging output
        $mail->Debugoutput = 'html'; // Show output in a readable format

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ugorjigideon2@gmail.com'; // Replace with your email
            $mail->Password = 'vveehxmeldoknxtg';    // Use an App Password, not your real password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email Setup
            $mail->setFrom('ugorjigideon2@gmail.com', 'VMS System');
            $mail->addAddress($visitor_email);
            $mail->Subject = "Appointment Approved";
            $mail->Body = "Your appointment has been approved. Your QR code is attached.";

            // Attach the QR code image
            $mail->addAttachment($qr_code_path);

            // Send Email
            if ($mail->send()) {
                echo "Visitor approved and QR Code sent successfully.";
            } else {
                echo "Email sending failed.";
            }
        } catch (Exception $e) {
            echo "Email sending failed: {$mail->ErrorInfo}";
        }
    } else {
        echo "Visitor not found.";
    }

    // Close database connection
    $stmt->close();
    $conn->close();
}
?>
