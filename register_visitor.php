<?php
session_start();  // Start the session to access session variables

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the employee is logged in
    if (isset($_SESSION['employee_id'])) {
        $employee_id = $_SESSION['employee_id']; // Get the logged-in employee's ID

        // Database connection
        $conn = new mysqli("localhost", "root", "", "visitor");

        // Check database connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Get the form data
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];

        // Handle image upload
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["picture"]["name"]);

        // Create the upload directory if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
            echo "File uploaded successfully.";
        } else {
            die("File upload failed. Check folder permissions.");
        }

        // Prepare and execute the SQL query to insert visitor data
        $stmt = $conn->prepare("INSERT INTO visitors (name, phone, email, picture, employee_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $phone, $email, $target_file, $employee_id);
        $stmt->execute();

        // Optional: Send email to CSO
        mail("cso@company.com", "New Visitor Entry", "A new visitor entry needs approval.");

        echo "Entry submitted. Awaiting approval.";
    } else {
        // If employee is not logged in, prompt them to log in first
        echo "You need to log in first.";
    }
}
?>

<style>
    /* Style for the form container */
    form {
        width: 300px;
        margin: 0 auto;
    }

    /* Make all form elements block-level for proper alignment */
    .form-group {
        margin-bottom: 10px;
    }

    .form-group label {
        display: block; /* Make labels block-level */
        font-weight: bold;
    }

    .form-group input {
        width: 100%; /* Make input fields take full width */
        padding: 8px;
        margin-top: 5px;
    }

    /* Style for submit button */
    input[type="submit"] {
        padding: 10px;
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
        width: 100%;
        font-size: 16px;
    }

    input[type="submit"]:hover {
        background-color: #45a049;
    }
</style>

<form action="register_visitor.php" method="POST" enctype="multipart/form-data">
    <!-- Visitor Name -->
    <div class="form-group">
        <label for="name">Visitor Name:</label>
        <input type="text" name="name" id="name" required>
    </div>

    <!-- Phone -->
    <div class="form-group">
        <label for="phone">Phone:</label>
        <input type="text" name="phone" id="phone" required>
    </div>

    <!-- Email -->
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>
    </div>

    <!-- Picture -->
    <div class="form-group">
        <label for="picture">Picture:</label>
        <input type="file" name="picture" id="picture" required>
    </div>

    <!-- Submit Button -->
    <input type="submit" value="Submit Entry">
</form>

