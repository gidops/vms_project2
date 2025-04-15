<?php
require 'db_connection.php';
// Manual PHPMailer inclusion
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

header('Content-Type: application/json');

// Email configuration (store these securely in production)
$mailConfig = [
    'host' => 'smtp.yourdomain.com',
    'username' => 'notifications@yourdomain.com',
    'password' => 'yourpassword',
    'port' => 587,
    'secure' => 'tls',
    'from_email' => 'reception@yourdomain.com',
    'from_name' => 'Visitor Management System'
];

// Constants for notification methods
define('NOTIFICATION_METHOD_EMAIL', 'email');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (!isset($data['visitor_id']) || !isset($data['employee_id'])) {
        throw new Exception('Missing visitor_id or employee_id');
    }

    // Fetch details
    $query = "SELECT 
                v.name AS visitor_name,
                v.organization,
                v.check_in_time,
                e.name AS employee_name,
                e.email AS employee_email
              FROM visitors v
              JOIN employees e ON v.employee_id = e.id
              WHERE v.id = ? AND e.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $data['visitor_id'], $data['employee_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('No matching visitor or employee found');
    }
    
    $details = $result->fetch_assoc();
    
    // Initialize PHPMailer
    $mail = new PHPMailer(true);
    $mailStatus = 'failed'; // Default status
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $mailConfig['smtp.gmail.com'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailConfig['ugorjigideon2@gmail.com'];
        $mail->Password   = $mailConfig['ahvnysmiwyjiervi'];
        $mail->SMTPSecure = $mailConfig[PHPMailer::ENCRYPTION_SMTPS];
        $mail->Port       = $mailConfig[465];
        
        // Recipients
        $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $mail->addAddress($details['employee_email'], $details['employee_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Visitor Arrival Notification';
        $mail->Body    = sprintf(
            '<h2>Visitor Arrival Notice</h2>
            <p>Hello %s,</p>
            <p>Your visitor <strong>%s</strong> from <strong>%s</strong> has arrived at %s.</p>
            <p>Please proceed to reception.</p>
            <p>Thank you!</p>',
            htmlspecialchars($details['employee_name']),
            htmlspecialchars($details['visitor_name']),
            htmlspecialchars($details['organization']),
            htmlspecialchars($details['check_in_time'])
        );
        
        $mail->AltBody = sprintf(
            "Hello %s,\n\nYour visitor %s from %s has arrived at %s.\n\nPlease proceed to reception.",
            $details['employee_name'],
            $details['visitor_name'],
            $details['organization'],
            $details['check_in_time']
        );
        
        $mail->send();
        $mailStatus = 'sent';
        $responseMessage = 'Employee notified via email';
    } catch (Exception $e) {
        $responseMessage = "Email could not be sent. Error: {$mail->ErrorInfo}";
    }
    
    // Log the notification
    $logQuery = "INSERT INTO notifications 
                (visitor_id, employee_id, method, status, created_at)
                VALUES (?, ?, ?, ?, NOW())";
    
    $logStmt = $conn->prepare($logQuery);
    
    // Define parameters as variables
    $method = NOTIFICATION_METHOD_EMAIL;
    $status = $mailStatus;
    
    $logStmt->bind_param(
        "iiss", 
        $data['visitor_id'],
        $data['employee_id'],
        $method,
        $status
    );
    
    if (!$logStmt->execute()) {
        throw new Exception("Failed to log notification: " . $logStmt->error);
    }
    
    echo json_encode([
        'success' => ($mailStatus === 'sent'),
        'message' => $responseMessage,
        'visitor' => $details['visitor_name'],
        'employee' => $details['employee_name'],
        'email' => $details['employee_email']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'System error: ' . $e->getMessage()
    ]);
}
?>