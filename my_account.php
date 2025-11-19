<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Ensure user is logged in, then load member profile, download history, donation history

// Checking if signed in
if (isset($_SESSION['member_id'])) {
    // DISPLAY MEMBER DETAILS
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

} else {
    header("Location: login.php");
}

?>
<h2>My Account</h2>
<p>TODO: Show member details, download and donation history.</p>
<?php include 'footer.php'; ?>
