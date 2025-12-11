<?php
session_start();
require 'db.php';
/*
- Marck Angelo GELI (40265711)
- Arshdeep SINGH (40286514)
- Muhammad Adnan SHAHZAD (40282531)
- Muhammad RAZA (40284058)
*/

// // Checks if user is an admin
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login.php");
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $charity_id = (int) $_POST['charity_id'];

    $sql_delete = "
        DELETE FROM charity
        WHERE charity_id = $charity_id
    ";

    $result_delete = mysqli_query($conn, $sql_delete);
    if ($result_delete) {
        $_SESSION['successful_charity_delete'] = "Charity has been deleted successfully!";
    } else {
        $_SESSION['failed_charity_delete'] = "Failed to delete charity.";
    }

    header("Location: admin_charities.php");
    exit;
} else {
    // If not a POST request, redirect back to charities page
    header("Location: admin_charities.php");
    exit;
}