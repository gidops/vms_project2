<?php
include 'db_connection.php';

if (isset($_POST['qr_data'])) {
    $qrData = $_POST['qr_data'];

    $query = "SELECT * FROM visitors WHERE qr_code = ? AND status = 'approved'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $qrData);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($visitor = $result->fetch_assoc()) {
        echo "✅ Visitor Found: " . htmlspecialchars($visitor['name']) . "|FOUND";
    } else {
        echo "❌ No guest found with the provided QR code.|NOT_FOUND";
    }

    $stmt->close();
    $conn->close();
}
?>

