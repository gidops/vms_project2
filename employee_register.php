<?php
// Start session to check login status later
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if input fields are not empty
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password'])) {
        echo "All fields are required!";
        exit;
    }

    // Establish a database connection
    $conn = new mysqli("localhost", "root", "", "visitor");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    // Check if the email already exists
    $stmt = $conn->prepare("SELECT id FROM employees WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Email already exists. Please choose another email.";
        exit;
    }

    // Prepare the insert query to add a new employee
    $stmt = $conn->prepare("INSERT INTO employees (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        echo "Employee registered successfully!";
        // Optionally redirect the user to another page
        // header("Location: login.php"); 
    } else {
        echo "Error registering employee: " . $stmt->error;
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
}
?>

<!-- HTML form for employee registration -->
<form action="folder/register_employee.php" method="POST">
    Name: <input type="text" name="name" required><br>
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" value="Register Employee">
</form>
