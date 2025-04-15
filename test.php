<?php
$conn = new mysqli("localhost", "root", "", "visitor");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
} else {
    echo "Database connected successfully!";
}
?>