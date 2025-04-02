<?php
$conn = new mysqli("localhost", "root", "", "visitor");

if (isset($_GET['qr_code'])) {
    $qr_code = $_GET['qr_code'];
    $result = $conn->query("SELECT * FROM visitors WHERE qr_code='$qr_code' AND status='approved'");

    if ($result->num_rows > 0) {
        echo "Access granted!";
    } else {
        echo "Access denied!";
    }
}
?>
