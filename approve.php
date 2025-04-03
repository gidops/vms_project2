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
    $qr_code_path = 'qr_codes/' . $qr_code . '.png';

    // Generate and save the QR code image
    QRcode::png($qr_code, $qr_code_path);

    // Update visitor status and assign QR code
    $stmt = $conn->prepare("UPDATE visitors SET status='approved', qr_code=? WHERE id=?");
    $stmt->bind_param("si", $qr_code, $id);
    $stmt->execute();
    //var_dump($id);
    

    // Retrieve visitor details
    $stmt = $conn->prepare("SELECT visitors.name, visitors.email, employees.email AS host_email FROM visitors LEFT JOIN employees ON visitors.host_id = employees.id WHERE visitors.id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    

    if ($result->num_rows > 0) {
        $visitor = $result->fetch_assoc();
        $visitor_name = $visitor['name'];
        $visitor_email = $visitor['email'];

        // Send email with PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ugorjigideon2@gmail.com';
            $mail->Password = 'vveehxmeldoknxtg';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email Setup
            $mail->setFrom('ugorjigideon2@gmail.com', 'VMS System');
            $mail->addAddress($visitor_email);
            $mail->Subject = "Appointment Approved - Visitor Management System";
            
            // HTML Email Content
            $mail->isHTML(true);
            $mail->Body = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
                        .content { padding: 20px; background-color: #f9f9f9; }
                        .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #777; }
                        .qr-code { text-align: center; margin: 20px 0; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Appointment Approved</h2>
                        </div>
                        <div class='content'>
                            <p>Dear $visitor_name,</p>
                            <p>Your appointment has been approved. Please find your QR code attached to this email.</p>
                            <div class='qr-code'>
                                <img src='cid:qr_code' alt='QR Code' style='width: 200px; height: 200px;'>
                            </div>
                            <p>Present this QR code at the reception when you arrive.</p>
                        </div>
                        <div class='footer'>
                            <p>Visitor Management System</p>
                            <p>This is an automated message, please do not reply.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            // Add embedded QR code image
            $mail->addEmbeddedImage($qr_code_path, 'qr_code');
            
            // Attach the QR code image as well
            $mail->addAttachment($qr_code_path, 'Your_QR_Code.png');

            // Send Email
            $mail->send();
            
            if (!empty($visitor['host_email'])) {
                $host_email = $visitor['host_email'];
    
                $host_mail = new PHPMailer(true);
            try {
                // SMTP Configuration
                $host_mail->isSMTP();
                $host_mail->Host = 'smtp.gmail.com';
                $host_mail->SMTPAuth = true;
                $host_mail->Username = 'ugorjigideon2@gmail.com';
                $host_mail->Password = 'vveehxmeldoknxtg';
                $host_mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $host_mail->Port = 587;

                // Email Setup
                $host_mail->setFrom('ugorjigideon2@gmail.com', 'VMS System');
                $host_mail->addAddress($host_email);
                $host_mail->Subject = "Your Visitor's Appointment Has Been Approved";
                $host_mail->isHTML(true);
                $host_mail->Body = "
                    <html>
                    <body>
                        <p>Dear Host,</p>
                        <p>Your visitor, <strong>$visitor_name</strong>, has been approved.</p>
                        <p>They will be arriving with the following QR code:</p>
                        <img src='cid:qr_code' alt='QR Code' style='width: 200px; height: 200px;'>
                        <p>Please be prepared for their visit.</p>
                    </body>
                    </html>
        ";

                // Attach the same QR code
                $host_mail->addEmbeddedImage($qr_code_path, 'qr_code');

                // Send the email
                $host_mail->send();
        } catch (Exception $e) {
        error_log("Host email sending failed: " . $host_mail->ErrorInfo);
    }
}

            // Display success message with Bootstrap styling
            echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Approval Success</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
                <style>
                    .success-container {
                        height: 100vh;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background-color: #f8f9fa;
                    }
                    .success-card {
                        max-width: 500px;
                        text-align: center;
                        padding: 30px;
                        border-radius: 10px;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    }
                </style>
            </head>
            <body>
                <div class="success-container">
                    <div class="success-card card">
                        <div class="card-body">
                            <div class="mb-4 text-success">
                                <i class="fas fa-check-circle fa-5x"></i>
                            </div>
                            <h2 class="card-title mb-3">Approval Successful</h2>
                            <p class="card-text mb-4">Visitor has been approved and the QR code has been sent to their email address.</p>
                            <div class="d-flex justify-content-center">
                                <a href="cso_dashboard.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i> Return to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
            ';
            
        } catch (Exception $e) {
            // Display error message with Bootstrap styling
            echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Approval Error</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
                <style>
                    .error-container {
                        height: 100vh;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background-color: #f8f9fa;
                    }
                    .error-card {
                        max-width: 500px;
                        text-align: center;
                        padding: 30px;
                        border-radius: 10px;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    }
                </style>
            </head>
            <body>
                <div class="error-container">
                    <div class="error-card card">
                        <div class="card-body">
                            <div class="mb-4 text-danger">
                                <i class="fas fa-exclamation-circle fa-5x"></i>
                            </div>
                            <h2 class="card-title mb-3">Approval Error</h2>
                            <p class="card-text mb-4">Email sending failed: ' . htmlspecialchars($mail->ErrorInfo) . '</p>
                            <div class="d-flex justify-content-center">
                                <a href="cso_dashboard.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i> Return to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
            </body>
            </html>
            ';
        }
    } else {
        // Display visitor not found error with Bootstrap styling
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Visitor Not Found</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        </head>
        <body>
            <div class="alert alert-danger m-4" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> Visitor not found.
            </div>
            <div class="m-4">
                <a href="cso_dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i> Return to Dashboard
                </a>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        ';
    }

    // Close database connection
    $stmt->close();
    $conn->close();
} else {
    // Display ID not provided error with Bootstrap styling
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <div class="alert alert-danger m-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> No visitor ID provided.
        </div>
        <div class="m-4">
            <a href="cso_dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> Return to Dashboard
            </a>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    ';
}
?>