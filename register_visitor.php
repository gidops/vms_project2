<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "visitor");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    // Handle image upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["picture"]["name"]);
    $upload_dir = "uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
}

$target_file = $upload_dir . basename($_FILES["picture"]["name"]);

if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
    echo "File uploaded successfully.";
} else {
    die("File upload failed. Check folder permissions.");
}


    $stmt = $conn->prepare("INSERT INTO visitors (name, phone, email, picture) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $email, $target_file);
    $stmt->execute();
    
    // Notify the Chief Security Officer (this can be an email alert)
    mail("cso@company.com", "New Visitor Entry", "A new visitor entry needs approval.");

    echo "Entry submitted. Awaiting approval.";
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

