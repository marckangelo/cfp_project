<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Ensure user is logged in, then load member profile, download history, donation history

// Checking if signed in
if (isset($_SESSION['member_id'])) {
    //DISPLAY MEMBER DETAILS
    $sql_member_details = "SELECT * FROM member";

    $result_member_details = mysqli_query(conn$, $sql_member_details);

    // Table header
    echo '
        
    ';

    while ($row = mysqli_fetch_assoc($result_member_details)) {

    }

} else {
    header("Location: login.php");
}

?>
<h2>My Account</h2>
<p>TODO: Show member details, download and donation history.</p>
<?php include 'footer.php'; ?>

<table>
    <tr>
        <th>Name</th>
        <th>organization</th>
        <th>Pseudonym</th>
        <th>Address: $row['street'], $row['city'], $row['country'], $row['postal_code']</th>
    </tr>
</table>
