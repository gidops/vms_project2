<?php
session_start();
require_once 'db_connection.php';

$errors = [];
$success = '';
$email = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data with null coalescing to prevent undefined index errors
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($email)) {
        $errors[] = "email is required";
    } elseif (strlen($email) < 4) {
        $errors[] = "email must be at least 4 characters";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Check if email already exists
            $checkStmt = $conn->prepare("SELECT id FROM cso WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkStmt->store_result();
            
            if ($checkStmt->num_rows > 0) {
                $errors[] = "email already exists";
            } else {
                // Hash password
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new CSO account
                $insertStmt = $conn->prepare("INSERT INTO cso (email, password_hash) VALUES (?, ?)");
                $insertStmt->bind_param("ss", $email, $passwordHash);
                
                if ($insertStmt->execute()) {
                    // Redirect to login page
                    header("Location: cso_login.php");
                    exit(); // Ensure script stops executing after redirect
                }
                 else {
                    $errors[] = "Error creating account: " . $conn->error;
                }
                $insertStmt->close();
            }
            $checkStmt->close();
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create CSO Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .password-match {
            font-size: 0.8rem;
            color: #28a745;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container bg-white">
            <h2 class="text-center mb-4">Create CSO Account</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="csoRegistrationForm">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="text-muted">Minimum 8 characters</small>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <div class="password-match mt-1">Passwords match!</div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Create Account</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchText = document.querySelector('.password-match');
            
            if (password && confirmPassword) {
                if (password === confirmPassword) {
                    matchText.style.display = 'block';
                    matchText.style.color = '#28a745';
                    matchText.textContent = 'Passwords match!';
                } else {
                    matchText.style.display = 'block';
                    matchText.style.color = '#dc3545';
                    matchText.textContent = 'Passwords do not match!';
                }
            } else {
                matchText.style.display = 'none';
            }
        });
    </script>
</body>
</html>