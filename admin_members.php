<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Admin-only member management (view, change status, etc.)

// Checking if signed in as member **** SHOULD BE CHECKING IF SIGNED IN AS ADMIN ****
if (isset($_SESSION['member_id'])) {
    // DISPLAY MEMBER DETAILS
    $sql_member_details = "SELECT * 
                           FROM member";
    
    // Run the query
    $result_member_details = mysqli_query($conn, $sql_member_details);

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
                <th>Status</th>
                <th>Action</th>
            </tr>
    ';
    
    //Table rows (Fetching each row from member detail using while loop)
    while ($row = mysqli_fetch_assoc($result_member_details)) {
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
                <td>' . htmlspecialchars($row['status']) . '</td>
                <td><button>Change Status<button></th>
            </tr>';
    }
    echo '</table>';
}

?>
<h2>Admin - Members</h2>
<p>TODO: List and manage members.</p>
<?php include 'footer.php'; ?>
