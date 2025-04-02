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
                // Check if email exists
                $stmt = $conn->prepare("SELECT id FROM employees WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $message = "Email already exists. Please choose another email.";
                    $message_class = "error";
                } else {
                    // Hash password
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    // Insert new employee
                    $stmt = $conn->prepare("INSERT INTO employees (name, email, password) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $name, $email, $password_hash);

                    if ($stmt->execute()) {
                        $message = "Registration successful! You can now login.";
                        $message_class = "success";
                        // Clear form fields
                        $name = $email = '';
                    } else {
                        $message = "Error registering employee: " . $stmt->error;
                        $message_class = "error";
                    }
                }
                $stmt->close();
                $conn->close();
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
        :root {
            --primary-color: #3498db;
            --primary-hover: #2980b9;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --light-gray: #f5f7fa;
            --dark-gray: #2c3e50;
            --text-color: #333;
            --border-radius: 8px;
            --box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-color);
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .registration-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
            transition: var(--transition);
            border-top: 5px solid var(--primary-color);
        }

        .registration-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .registration-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .registration-header h2 {
            color: var(--dark-gray);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .registration-header p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .password-strength {
            height: 4px;
            background-color: #eee;
            margin-top: 0.5rem;
            border-radius: 2px;
            overflow: hidden;
            position: relative;
        }

        .password-strength::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0%;
            background-color: var(--error-color);
            transition: var(--transition);
        }

        .password-strength.weak::before {
            width: 30%;
            background-color: var(--error-color);
        }

        .password-strength.medium::before {
            width: 60%;
            background-color: #f39c12;
        }

        .password-strength.strong::before {
            width: 100%;
            background-color: var(--success-color);
        }

        .password-hint {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-top: 0.3rem;
            display: none;
        }

        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem;
            width: 100%;
            border-radius: var(--border-radius);
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .message {
            text-align: center;
            margin: 1.5rem 0;
            padding: 1rem;
            border-radius: var(--border-radius);
        }

        .error {
            background-color: #ffebee;
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }

        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid var(--success-color);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .input-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            cursor: pointer;
        }

        @media (max-width: 576px) {
            .registration-container {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-header">
            <h2>Create Your Account</h2>
            <p>Join our team by filling out the registration form</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_class; ?>">
                <i class="fas <?php echo $message_class === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="employee_register.php" method="POST" id="registrationForm">
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
                <i class="fas fa-user-plus me-2"></i> <a href="employee_login.php">Register Now</a>
            </button>
        </form>

        <div class="login-link">
            Already have an account? <a href="employee_login.php">Sign in here</a>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const hint = document.getElementById('passwordHint');
            
            // Reset
            strengthBar.className = 'password-strength';
            hint.style.display = 'none';
            
            if (password.length > 0) {
                hint.style.display = 'block';
                
                // Check password strength
                let strength = 0;
                if (password.length >= 8) strength++;
                if (password.match(/[a-z]/)) strength++;
                if (password.match(/[A-Z]/)) strength++;
                if (password.match(/[0-9]/)) strength++;
                if (password.match(/[^a-zA-Z0-9]/)) strength++;
                
                // Update strength bar
                if (strength <= 2) {
                    strengthBar.classList.add('weak');
                } else if (strength <= 4) {
                    strengthBar.classList.add('medium');
                } else {
                    strengthBar.classList.add('strong');
                }
            }
        });

        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            
            if (password.length < 8) {
                alert('Password must be at least 8 characters long');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>