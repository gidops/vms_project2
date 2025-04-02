<?php
$conn = new mysqli("localhost", "root", "", "visitor");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all pending entries
$result = $conn->query("SELECT * FROM visitors WHERE status='pending'");

while ($row = $result->fetch_assoc()) {
    echo "<p>{$row['name']} - {$row['phone']} - {$row['email']}</p>";
    echo "<img src='{$row['picture']}' width='100'><br>";
    echo "<a href='approve.php?id={$row['id']}'>Approve</a> | ";
    echo "<a href='deny.php?id={$row['id']}'>Deny</a><hr>";
}
?>
