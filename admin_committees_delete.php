<?php
session_start();
require 'db.php';
// include 'header.php';

// // Checks if user is an admin
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login.php");
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $committee_id = (int) $_POST['committee_id'];

    // Build the MySql DELETE query
    $sql_delete = "
        DELETE FROM committee
        WHERE committee_id = $committee_id
    ";

    // Run the DELETE query
    $result_delete = mysqli_query($conn, $sql_delete);

    if ($result_delete) {
        $_SESSION['successful_committee_delete'] = "Committee has been deleted successfully!";
    } else {
        $_SESSION['failed_committee_delete'] = "Failed to delete committee.";
    }

    header("Location: admin_committees.php");
    exit;
} else {
    // If not a POST request, redirect back to committees page
    header("Location: admin_committees.php");
    exit;
}