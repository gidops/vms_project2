<?php
session_start();
session_destroy();
header("Location: employee_login.php"); // Adjust to the path of your login page
exit();
