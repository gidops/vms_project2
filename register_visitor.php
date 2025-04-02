<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if employee is logged in
    if (isset($_SESSION['employee_id'])) {
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

        // Get form data
        $name = $conn->real_escape_string($_POST['name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);

        // Handle image upload
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = uniqid() . '_' . basename($_FILES["picture"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is actual image
        $check = getimagesize($_FILES["picture"]["tmp_name"]);
        if ($check === false) {
            die("
                <div class='alert alert-danger' role='alert'>
                    <i class='fas fa-image me-2'></i> File is not an image.
                </div>
            ");
        }

        // Check file size (max 2MB)
        if ($_FILES["picture"]["size"] > 2000000) {
            die("
                <div class='alert alert-danger' role='alert'>
                    <i class='fas fa-file-image me-2'></i> Image size must be less than 2MB.
                </div>
            ");
        }

        // Allow certain file formats
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowed_types)) {
            die("
                <div class='alert alert-danger' role='alert'>
                    <i class='fas fa-file-image me-2'></i> Only JPG, JPEG, PNG & GIF files are allowed.
                </div>
            ");
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO visitors (name, phone, email, picture, employee_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $name, $phone, $email, $target_file, $employee_id);
            
            if ($stmt->execute()) {
                // Send email to CSO (placeholder - implement your email function)
                // mail("cso@company.com", "New Visitor Entry", "A new visitor entry needs approval.");
                
                $success_message = "
                    <div class='alert alert-success' role='alert'>
                        <i class='fas fa-check-circle me-2'></i> 
                        Visitor registered successfully! Awaiting approval.
                    </div>
                ";
            } else {
                $error_message = "
                    <div class='alert alert-danger' role='alert'>
                        <i class='fas fa-exclamation-circle me-2'></i> 
                        Error: " . htmlspecialchars($stmt->error) . "
                    </div>
                ";
            }
            $stmt->close();
        } else {
            $error_message = "
                <div class='alert alert-danger' role='alert'>
                    <i class='fas fa-exclamation-triangle me-2'></i> 
                    Sorry, there was an error uploading your file.
                </div>
            ";
        }
        $conn->close();
    } else {
        $error_message = "
            <div class='alert alert-warning' role='alert'>
                <i class='fas fa-exclamation-triangle me-2'></i> 
                You need to log in first.
            </div>
        ";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Registration</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .registration-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-header {
            background-color: #0d6efd;
            color: white;
            border-radius: 10px 10px 0 0;
        }
        .file-upload {
            position: relative;
            overflow: hidden;
        }
        .file-upload-input {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
        }
        .file-upload-label {
            cursor: pointer;
            display: block;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px dashed #dee2e6;
            border-radius: 5px;
            text-align: center;
        }
        .file-upload-label:hover {
            background-color: #e9ecef;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card registration-card">
                    <div class="card-header form-header text-center py-3">
                        <h3><i class="fas fa-user-plus me-2"></i> Visitor Registration</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php 
                        if (isset($success_message)) echo $success_message;
                        if (isset($error_message)) echo $error_message;
                        ?>
                        
                        <form action="register_visitor.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Visitor Photo</label>
                                <div class="file-upload">
                                    <label class="file-upload-label" for="picture">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                        <span id="file-name">Click to upload photo</span>
                                    </label>
                                    <input type="file" class="file-upload-input" id="picture" name="picture" accept="image/*" required>
                                    <img id="preview" class="preview-image img-thumbnail" alt="Preview">
                                </div>
                                <div class="form-text">Max file size: 2MB (JPG, PNG, GIF)</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Register Visitor
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Preview image before upload
        document.getElementById('picture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById('file-name').textContent = file.name;
                
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('preview');
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>