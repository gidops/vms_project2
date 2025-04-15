<?php
// checkin_visitor.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';



if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['qr_data'])) {
    $qrData = $_POST['qr_data'];

    // DB connection
    $conn = new mysqli("localhost", "root", "", "visitor");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Look up visitor by QR data (assumed to be email or unique token)
    $stmt = $conn->prepare("SELECT * FROM visitors WHERE email = ? AND status = 'approved'");
    $stmt->bind_param("s", $qrData);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Update status to 'checked_in'
        $update = $conn->prepare("UPDATE visitors SET status = 'checked_in' WHERE email = ?");
        $update->bind_param("s", $qrData);
        $update->execute();

        // Send email to host
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ugorjigideon2@gmail.com';
            $mail->Password = 'tzfitrlpqegfcmag';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;


            $mail->setFrom('your_email@example.com', 'Visitor Management System');
            $mail->addAddress($row['host_email'], $row['host_name']);
            $mail->Subject = 'Visitor Check-In';
            $mail->Body = "Your visitor " . $row['name'] . " has been checked in.";

            $mail->send();
        } catch (Exception $e) {
            error_log("Email error: " . $mail->ErrorInfo);
        }

        echo "Visitor " . htmlspecialchars($row['name']) . " checked in and host notified.";
    } else {
        echo "Visitor record not found or already checked in.";
    }

    $stmt->close();
    $conn->close();
}
?>
