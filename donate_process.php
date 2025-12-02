<?php
/*
Contributor to this file:
- Muhammad Adnan SHAHZAD (40282531)
*/

session_start();
require 'db.php';

// TODO: Process donation form, insert into database, enforce allocation rules

if (isset($_SESSION['member_id'])) {

// CODE 

// Extract data to insert into database
$member_id = (int) $_SESSION['member_id'];
$text_id = (int) $_POST['text_id'];
$charity_id = (int) $_POST['charity_id'];
$amount = (float) $_POST['amount'];
$currency = $_POST['currency'];
$payment_method = $_POST['payment_method'];
$transaction_id = generate_transaction_id();

$charity_pct = (int) $_POST['charity_pct'];
$cfp_pct = (int) $_POST['cfp_pct'];
$author_pct = (int) $_POST['author_pct'];

// Get title of the text you are donating to through POST
$text_title = $_POST['title'];

//Validate the % data and amount

// Amount must be > 0
if ($amount <= 0) {
    $_SESSION['failed_donation'] = htmlspecialchars("Failed donated to '$text_title'! Donation amount must be greater than 0.");
    header("Location: item.php?text_id=$text_id");
    exit;
}

// Charity must be at least 60%
if ($charity_pct < 60) {
    $_SESSION['failed_donation'] = htmlspecialchars("Failed donated to '$text_title'! Charity percentage must be at least 60%.");
    header("Location: item.php?text_id=$text_id");
    exit;
}

// CFP and Author between 0 and 40 (to match the HTML constraints)
if ($cfp_pct < 0 || $cfp_pct > 40 || $author_pct < 0 || $author_pct > 40) {
    $_SESSION['failed_donation'] = htmlspecialchars("Failed donated to '$text_title'! CFP and Author percentages must be between 0% and 40%.");
    header("Location: item.php?text_id=$text_id");
    exit;
}

$total_pct = $charity_pct + $cfp_pct + $author_pct;

/*  charity_pct is already validated through html requirements (60% <= charity_pct <= 100%) in donate.php file
    
    Just needs to validate whether all 3 percentage values add up to 100%, if not, go back to item.php 
    where you can click on the 'Donate' buttton again
*/
if ($total_pct !== 100) {
    $_SESSION['failed_donation'] = htmlspecialchars("Failed donated to '$text_title'! Charity, Author and CFP percentages must add up to 100% (you have $total_pct%)."); 
    header("Location: item.php?text_id=$text_id");
    exit;
}

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

// if Queries are good, head back to proper page after donating and display green success message on that page.
if ($result_donation) {

    // -------- Update charity.total_received --------
    $amount       = (float)$amount; // Cast to float, just in case
    $charity_pct  = (float)$charity_pct; // Cast to float, just in case


    $charity_part = (float)$amount * ((float)$charity_pct / 100.0);

    // Update the total_received for this charity
    $sql_update_charity = "
        UPDATE charity
        SET total_received = total_received + $charity_part
        WHERE charity_id = $charity_id
    ";

    mysqli_query($conn, $sql_update_charity);

    // // -------- Update author.total_received --------
    // // First get the author_orcid for this text
    // $sql_get_author = "
    //     SELECT author_orcid
    //     FROM text
    //     WHERE text_id = $text_id
    // ";
    // $result_author = mysqli_query($conn, $sql_get_author);

    // if ($result_author && mysqli_num_rows($result_author) > 0) {
    //     $row_author   = mysqli_fetch_assoc($result_author);
    //     $author_orcid = mysqli_real_escape_string($conn, $row_author['author_orcid']);

    //     // Author’s share of the donation
    //     $author_part = $amount * ($author_pct / 100.0);

    //     $sql_update_author = "
    //         UPDATE author
    //         SET total_received = total_received + $author_part
    //         WHERE orcid = '$author_orcid'
    //     ";
    //     mysqli_query($conn, $sql_update_author);
    // }

    $_SESSION['successful_donation'] = "Successfully donated to '$text_title' !";

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