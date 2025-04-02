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

<form action="register_visitor.php" method="POST" enctype="multipart/form-data">
    Name: <input type="text" name="name" required><br>
    Phone: <input type="text" name="phone" required><br>
    Email: <input type="email" name="email" required><br>
    Picture: <input type="file" name="picture" required><br>
    <input type="submit" value="Submit Entry">
</form>
