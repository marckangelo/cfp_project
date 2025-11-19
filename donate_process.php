<?php
session_start();
require 'db.php';

// TODO: Process donation form, insert into database, enforce allocation rules

if (isset($_SESSION['member_id'])) {

// CODE 

// Extract data to insert into database
$member_id = $_SESSION['member_id'];
$text_id = $_POST['text_id'];
$charity_id = $_POST['charity_id'];
$amount = $_POST['amount'];
$currency = $_POST['currency'];
$payment_method = $_POST['payment_method'];
$transaction_id = generate_transaction_id();
$charity_pct = $_POST['charity_pct'];
$cfp_pct = $_POST['cfp_pct'];
$author_pct = $_POST['author_pct'];

//Validate the data

// If data is valid build SQL query to insert

$sql_donation = "INSERT INTO donation 
                        (member_id, 
                        text_id, 
                        charity_id, 
                        amount, 
                        date, 
                        currency, 
                        payment_method, 
                        transaction_id, 
                        charity_pct, 
                        cfp_pct, 
                        author_pct)
             VALUES (
                        $member_id,
                        $text_id,
                        $charity_id,
                        $amount,
                        NOW(),
                        '$currency',
                        '$payment_method',
                        '$transaction_id',
                        $charity_pct, 
                        $cfp_pct, 
                        $author_pct)";

$result_donation = mysqli_query($conn, $sql_donation);

// if statements to validate query
        // if Queries are good, head back to proper page after donating and display green success message on that page.
if ($result_donation) {
    header("Location: my_account.php");
    exit;
}


} else {
    header("Location: login.php");
    exit;
}


// function for generating a random unique transaction_id
function generate_transaction_id(): string {
    $transaction_id_string = "";
    
    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

    for($i=0; $i < 3; $i++) {
        for ($j=0; $j < 4; $j++) { 
        $random_index = rand(0, strlen($chars)-1);

        $transaction_id_string .= $chars[$random_index];
        }

        if ($i == 2) {
            break;
        }
        else {
            $transaction_id_string .= "-";
        }   
    }

    return 'DONA-' . $transaction_id_string;
}
?>
