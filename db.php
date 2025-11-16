<?php
// Basic database connection (procedural style)
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "cfp_db";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>