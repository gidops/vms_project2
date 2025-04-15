<?php
require 'db_connection.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['visitor_id']) || !isset($data['qr_data'])) {
        throw new Exception('Missing required fields');
    }

    $query = "UPDATE visitors SET 
              check_in_time = NOW(),
              arrival_time = TIME(NOW()),
              arrival_date = CURDATE(),
              status = 'checked_in',
              visit_date = CURDATE()
              WHERE id = ? 
              AND qr_code = ? 
              AND status = 'approved'";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $data['visitor_id'], $data['qr_data']);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Visitor checked in successfully',
                'check_in_time' => date('Y-m-d H:i:s'),
                'arrival_time' => date('H:i:s')
            ]);
        } else {
            // No rows affected means either:
            // - QR code didn't match
            // - Status wasn't 'approved'
            // - Already checked in
            throw new Exception('Visitor not approved or already checked in');
        }
    } else {
        throw new Exception('Database update failed');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>