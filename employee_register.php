<?php
session_start();

// Initialize variables
$message = '';
$message_class = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password'])) {
        $message = "All fields are required!";
        $message_class = "error";
    } else {
        // Sanitize inputs
        $name = htmlspecialchars(trim($_POST['name']));
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format!";
            $message_class = "error";
        } else {
            // Establish database connection
            $conn = new mysqli("localhost", "root", "", "visitor");

            if ($conn->connect_error) {
                $message = "Database connection failed: " . $conn->connect_error;
                $message_class = "error";
            } else {
                // Initialize $stmt as null
                $stmt = null;
                $emailExists = false;
                
                try {
                    // Check if email exists
                    $stmt = $conn->prepare("SELECT id FROM employees WHERE email = ?");
                    if (!$stmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
                    
                    $stmt->bind_param("s", $email);
                    if (!$stmt->execute()) {
                        throw new Exception("Execute failed: " . $stmt->error);
                    }
                    
                    $stmt->store_result();
                    $emailExists = $stmt->num_rows > 0;
                    $stmt->close();
                    
                    if ($emailExists) {
                        $message = "Email already exists. Please choose another email.";
                        $message_class = "error";
                    } else {
                        // Hash password
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);

                        // Insert new employee
                        $stmt = $conn->prepare("INSERT INTO employees (name, email, password) VALUES (?, ?, ?)");
                        if (!$stmt) {
                            throw new Exception("Prepare failed: " . $conn->error);
                        }
                        
                        $stmt->bind_param("sss", $name, $email, $password_hash);
                        
                        if ($stmt->execute()) {
                            // Set session variable and redirect
                            $_SESSION['registration_success'] = true;
                            header("Location: employee_login.php");
                            exit();
                        } else {
                            throw new Exception("Execute failed: " . $stmt->error);
                        }
                    }
                } catch (Exception $e) {
                    $message = "Error: " . $e->getMessage();
                    $message_class = "error";
                } finally {
                    if ($stmt) {
                        $stmt->close();
                    }
                    $conn->close();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* [Keep all your existing CSS styles] */
        .registration-container {
            position: relative;
        }
        .no-login-message {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-header">
            <h2>Create Your Account</h2>
            <p>Registration is required to access the system</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_class; ?>">
                <i class="fas <?php echo $message_class === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="registrationForm">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required 
                       placeholder="John Doe" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                <i class="fas fa-user input-icon"></i>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required 
                       placeholder="john@example.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                <i class="fas fa-envelope input-icon"></i>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required 
                       placeholder="At least 8 characters">
                <i class="fas fa-eye input-icon" id="togglePassword"></i>
                <div class="password-strength" id="passwordStrength"></div>
                <div class="password-hint" id="passwordHint">
                    Password must contain at least 8 characters, including uppercase, lowercase, and numbers
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-user-plus me-2"></i> Register Now
            </button>
        </form>

        <div class="no-login-message">
            Please complete registration to access the system
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // [Keep all your existing JavaScript code]
    </script>
</body>
</html>