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


<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f7fa;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        color: #333;
    }

    .registration-container {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 100%;
        max-width: 400px;
        transition: transform 0.3s ease;
    }

    .registration-container:hover {
        transform: translateY(-5px);
    }

    h2 {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 25px;
        font-weight: 600;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #555;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 16px;
        transition: border 0.3s;
        box-sizing: border-box;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus {
        border-color: #3498db;
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    }

    .submit-btn {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 12px 20px;
        width: 100%;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .submit-btn:hover {
        background-color: #2980b9;
    }

    .message {
        text-align: center;
        margin-top: 20px;
        padding: 10px;
        border-radius: 5px;
    }

    .error {
        background-color: #ffebee;
        color: #c62828;
    }

    .success {
        background-color: #e8f5e9;
        color: #2e7d32;
    }

    .login-link {
        text-align: center;
        margin-top: 20px;
        font-size: 14px;
    }

    .login-link a {
        color: #3498db;
        text-decoration: none;
    }

    .login-link a:hover {
        text-decoration: underline;
    }
</style>

<div class="registration-container">
    <h2>Employee Registration</h2>
    <?php if(isset($message)): ?>
        <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <form action="employee_register.php" method="POST">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required placeholder="Enter your full name">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Create a password">
        </div>
        <button type="submit" class="submit-btn">Register</button>
    </form>
    <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>
