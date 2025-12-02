<?php
// Basic database connection (procedural style)
$servername = "dvc353.encs.concordia.ca";
$username   = "dvc353_2";
$password   = "fuzzymist97";
$dbname     = "dvc353_2";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>