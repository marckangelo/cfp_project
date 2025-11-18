<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: On POST, validate allocation and insert donation
$errors = array();
$success = "";

echo "<h2>Donate</h2>";
echo "<p>";
//TODO: Implement donation form (amount and allocation to charity/CFP/author);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //retreive from data
    $member_id = $_SESSION['member_id'];
    $text_id = trim($_POST['text_id']);
    $charity_id = trim($_POST['charity_id']);
    $amount = trim($_POST['amount']);
    $date = trim($_POST['date']);
    $currency = trim($_POST['currency']);
    $payment_method = trim($_POST['payment_method']);
    $transaction_id = trim($_POST['transaction_id']);
    $charity_pct = trim($_POST['charity_pct']);
    $cfp_pct = trim($_POST['cfp_pct']);
    $author_pct = trim($_POST['author_pct']);

    // Basic validation
    if ($amount == "" || !is_numeric($amount) || $amount <= 0) {
        $errors[] = "An amount is required and must be a positive number.";
    }

    //if no errors, insert donation
    if (count ($errors) == 0) {
        $query = "INSERT INTO donation (member_id, text_id, charity_id, amount, date, currency, payment_method, transaction_id, charity_pct, cfp_pct, author_pct)
                  VALUES ($member_id, $text_id, $charity_id, $amount, '$date', '$currency', '$payment_method', '$transaction_id', $charity_pct, $cfp_pct, $author_pct)";
        if (mysqli_query($conn, $query)) {
            $success = "Donation recorded successfully.";
        } 
    }

}


include 'footer.php'; ?>