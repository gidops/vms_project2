<?php


session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") 
    // Check if employee is logged in
    if (isset($_SESSION['employee_id'])) {}
        $employee_id = $_SESSION['employee_id'];

        // Database connection
        $conn = new mysqli("localhost", "root", "", "visitor");

        // Check connection
        if ($conn->connect_error) {
            die("
                <div class='alert alert-danger' role='alert'>
                    <i class='fas fa-database me-2'></i> Connection failed: " . htmlspecialchars($conn->connect_error) . "
                </div>
            ");
        }

// Hash password before storing
$password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, password_hash, email) 
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", 
    $_POST['username'], 
    $password_hash,
    $_POST['email']
);
$stmt->execute();
?>