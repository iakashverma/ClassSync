<?php
// Database connection file
// Using MySQLi to connect to our classsync database

$host = "localhost";
$username = "root";
$password = "";  // default XAMPP has no password
$database = "classsync";

// create connection
$conn = mysqli_connect($host, $username, $password, $database);

// check if connection worked
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// set charset to avoid encoding issues
mysqli_set_charset($conn, "utf8");
?>
