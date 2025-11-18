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

// Check if user is logged in
if ($_SESSION['member_id'] == "") {
    echo "You must be logged in to make a donation.";
    include 'footer.php';
    exit();
}

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
    if ($text_id == "" || !is_numeric($text_id)) {
        $errors[] = "Text ID must be a number if provided.";
    }

    if ($charity_id == "" || !is_numeric($charity_id)) {
        $errors[] = "Charity ID must be a number if provided.";
    }

    if ($amount == "" || !is_numeric($amount) || $amount <= 0) {
        $errors[] = "An amount is required and must be a positive number.";
    }

    // Set date to today if not provided
    if ($date  == "") {
        $date = date('Y-m-d');
    }

    $currency = ($currency == "") ? "NULL" : $currency;
    $payment_method = ($payment_method == "") ? "" : $payment_method;
    $transaction_id = ($transaction_id == "") ? "" : $transaction_id;
    $charity_pct = ($charity_pct == "") ? NULL : intval($charity_pct);
    $cfp_pct = ($cfp_pct == "") ? NULL : intval($cfp_pct);
    $author_pct = ($author_pct == "") ? NULL : intval($author_pct);

    //if no errors, insert donation
    if (count($errors) == 0) {
        $query = "INSERT INTO donation (member_id, text_id, charity_id, amount, date, currency, payment_method, transaction_id, charity_pct, cfp_pct, author_pct)
                  VALUES ($member_id, $text_id, $charity_id, $amount, '$date', '$currency', '$payment_method', '$transaction_id', $charity_pct, $cfp_pct, $author_pct)";
        if (mysqli_query($conn, $query)) {
            $success = "Donation recorded successfully.";
        } else {
            $errors[] = "Error recording donation: " . mysqli_error($conn);
        }
        if (count($errors) > 0) {
            echo '<div style="color:red;"><ul>';
            for ($i = 0; $i < count($errors); $i++) {
                echo '<li>' . $errors[$i] . '</li>';
            }
            echo '</ul></div>';
        } else {
            echo '<div style="color:green;">' . $success . '</div>';
        }
    }
} ?>

<form method="post" action="donate.php">

    <label>Text ID (required):
        <input type="text" name="text_id" required>
    </label><br>

    <label>Charity ID (required):
        <input type="text" name="charity_id" required>
    </label><br>

    <label>Amount (required):
        <input type="text" name="amount" required>
    </label><br>

    <label>Date (YYYY-MM-DD, default today):
        <input type="text" name="date">
    </label><br>

    <label>Currency:
        <input type="text" name="currency">
    </label><br>

    <label>Payment Method:
        <input type="text" name="payment_method">
    </label><br>

    <label>Transaction ID:
        <input type="text" name="transaction_id">
    </label><br>

    <label>Charity Percentage:
        <input type="text" name="charity_pct">
    </label><br>

    <label>CFP Percentage:
        <input type="text" name="cfp_pct">
    </label><br>

    <label>Author Percentage:
        <input type="text" name="author_pct">
    </label><br>

    <input type="submit" value="Donate">

<?php
include 'footer.php';
?>