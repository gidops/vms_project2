<?php
$password_hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed Password: " . $password_hash;
?>