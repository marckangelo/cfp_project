<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Ensure user is logged in, then load member profile, download history, donation history

// Show download success message, if any
if(isset($_SESSION['download_success'])) {
    echo '<div style="color:green;">' . $_SESSION['download_success'] . '</div>';
    unset($_SESSION['download_success']);
}

// Show download error message, if any
if(isset($_SESSION['download_failure'])) {
    echo '<div style="color:red;">' . $_SESSION['download_failure'] . '</div>';
    unset($_SESSION['download_failure']);
}

// Checking if signed in
if (isset($_SESSION['member_id'])) {
    // ================ DISPLAY MEMBER DETAILS =================
    $sql_member_details = "SELECT * 
                           FROM member
                           WHERE member_id = " . $_SESSION['member_id'];
    
    // Run the query
    $result_member_details = mysqli_query($conn, $sql_member_details);

    // Fetch the data
    $row = mysqli_fetch_assoc($result_member_details);

    // Table header
    echo '
    <h4>Member Details</h4>

        <table border="1">
            <tr>
                <th>Name</th>
                <th>organization</th>
                <th>Pseudonym</th>
                <th>Address</th>
                <th>Primary Email</th>
                <th>Recovery Email</th>
                <th>Download Limit</th>
            </tr>
    ';
    
    //Table rows
        echo '
            <tr>
                <td>' . htmlspecialchars($row['name']) . '</td>
                <td>' . htmlspecialchars($row['organization']) . '</td>
                <td>' . htmlspecialchars($row['pseudonym']) . '</td>
                <td>' . 
                    htmlspecialchars($row['street']) . ', ' . 
                    htmlspecialchars($row['city']) . ', ' . 
                    htmlspecialchars($row['country']) . ', ' . 
                    htmlspecialchars($row['postal_code']) . 
                '</td>    
                <td>' . htmlspecialchars($row['primary_email']) . '</td>
                <td>' . htmlspecialchars($row['recovery_email']) . '</td>
                <td>' . htmlspecialchars($row['download_limit']) . '</td>
            </tr>
        ';
    echo '</table>';

    echo'
        <form method="post" action="edit_profile.php">
            <input type="hidden" name="member_id" value="'. $row['member_id'] . '">
            <button type="submit" name="edit_profile">Edit</button>
        </form>
    ';



    // ============= DISPLAY LIST OF DOWNLOADS ================

    // DISPLAY MEMBER DETAILS
    $member_id = $_SESSION['member_id'];

    $sql_download_details = "SELECT 
                                d.download_date,
                                t.text_id,
                                t.title,
                                t.topic,
                                t.version,
                                t.status,
                                a.orcid AS author_orcid,
                                m.name AS author_name
                             FROM download d
                             JOIN text t
                                ON d.text_id = t.text_id
                             JOIN author a
                                ON t.author_orcid = a.orcid
                             JOIN member m
                                ON a.member_id = m.member_id
                             WHERE d.member_id = $member_id
                             ORDER BY d.download_date DESC";
    
    // Run the query
    $result_download_details = mysqli_query($conn, $sql_download_details);

    // Table header
    echo '
    <h4>Download Details</h4>

        <table border="1">
            <tr>
                <th>Download Date</th>
                <th>Title</th>
                <th>Topic</th>
                <th>Version</th>
                <th>Status</th>
                <th>Author</th>
            </tr>
    ';
    
    //Table rows (Fetching each row from member detail using while loop)
    while ($row_download = mysqli_fetch_assoc($result_download_details)) {
        echo '
            <tr>
                <td>' . htmlspecialchars($row_download['download_date']) . '</td>
                <td>' . htmlspecialchars($row_download['title']) . '</td>
                <td>' . htmlspecialchars($row_download['topic']) . '</td>
                <td>' . htmlspecialchars($row_download['version']) . '</td>
                <td>' . htmlspecialchars($row_download['status']) . '</td>
                <td>' . htmlspecialchars($row_download['author_name']) . '</td>
                <td><button>Change Status<button></th>
            </tr>';
    }
    echo '</table>';


    // ============= DISPLAY LIST OF DONATIONS ================

    // DISPLAY DONATION DETAILS
    $member_id = $_SESSION['member_id'];

    $sql_donation_details = "SELECT d.date,
                                    d.amount,
                                    d.currency,
                                    d.payment_method,
                                    d.transaction_id,
                                    d.charity_pct,
                                    d.cfp_pct,
                                    d.author_pct,
                                    
                                    t.title AS text_title,
                                    t.topic AS text_topic,
                                    t.version AS text_version,
                                    
                                    c.name AS charity_name,
                                    c.country AS charity_country,
                                    c.description AS charity_description,
                                    c.mission AS charity_mission
                             FROM donation d
                             JOIN text t
                                 ON d.text_id = t.text_id
                             JOIN charity c
                                 ON d.charity_id = c.charity_id
                             WHERE d.member_id = $member_id
                             ORDER BY d.date DESC";

    
    // Run the query
    $result_donation_details = mysqli_query($conn, $sql_donation_details);

    // Table header
    echo '
    <h4>Donation Details</h4>

        <table border="1">
            <tr>
                <th>Donation Date</th>
                <th>Amount</th>
                <th>Currency</th>
                <th>Payment Method</th>
                <th>TransactionID</th>
                <th>Charity (%)</th>
                <th>CFP (%)</th>
                <th>Author (%)</th>
                
                <th>Title</th>
                <th>Topic</th>
                <th>Text Version</th>

                <th>Charity Name</th>
                <th>Charity Country</th>
                <th>Charity Description</th>
                <th>Charity Mission</th>
            </tr>
    ';
    
    //Table rows (Fetching each row from member detail using while loop)
    while ($row_donation = mysqli_fetch_assoc($result_donation_details)) {
        echo '
            <tr>
                <td>' . htmlspecialchars($row_donation['date']) . '</td>
                <td>' . htmlspecialchars($row_donation['amount']) . '</td>
                <td>' . htmlspecialchars($row_donation['currency']) . '</td>
                <td>' . htmlspecialchars($row_donation['payment_method']) . '</td>
                <td>' . htmlspecialchars($row_donation['transaction_id']) . '</td>
                <td>' . htmlspecialchars($row_donation['charity_pct']) . '</td>
                <td>' . htmlspecialchars($row_donation['cfp_pct']) . '</td>
                <td>' . htmlspecialchars($row_donation['author_pct']) . '</td>
                
                <td>' . htmlspecialchars($row_donation['text_title']) . '</td>
                <td>' . htmlspecialchars($row_donation['text_topic']) . '</td>
                <td>' . htmlspecialchars($row_donation['text_version']) . '</td>
                
                <td>' . htmlspecialchars($row_donation['charity_name']) . '</td>
                <td>' . htmlspecialchars($row_donation['charity_country']) . '</td>
                <td>' . htmlspecialchars($row_donation['charity_description']) . '</td>
                <td>' . htmlspecialchars($row_donation['charity_mission']) . '</td>
            </tr>';
    }
    echo '</table>';

} else {
    header("Location: login.php");
    exit;
}



?>
<h2>My Account</h2>
<p>TODO: Show member details, download and donation history.</p>
<?php include 'footer.php'; ?>
