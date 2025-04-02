<?php
$conn = new mysqli("localhost", "root", "", "visitor");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("UPDATE visitors SET status='denied' WHERE id=$id");

    echo "Visitor entry denied.";
}
?>
