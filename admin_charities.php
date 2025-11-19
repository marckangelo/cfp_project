<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Admin-only charities management

// Checking if signed in
if (isset($_SESSION['member_id'])) {
    // DISPLAY MEMBER DETAILS
    $sql_charity_details = "SELECT * 
                           FROM charity";
    
    // Run the query
    $result_charity_details = mysqli_query($conn, $sql_charity_details);

    // Table header
    echo '
    <h4>Charity Details</h4>

        <table border="1">
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Mission</th>
                <th>Country</th>
                <th>Registration Number</th>
                <th>Status</th>
                <th>Total Received ($)</th>
                <th>Action</th>
            </tr>
    ';
    
    //Table rows (Fetching each row from member detail using while loop)
    while ($row = mysqli_fetch_assoc($result_charity_details)) {
        echo '
            <tr>
                <td>' . htmlspecialchars($row['name']) . '</td>
                <td>' . htmlspecialchars($row['description']) . '</td>
                <td>' . htmlspecialchars($row['mission']) . '</td>
                <td>' . htmlspecialchars($row['country']) . '</td>    
                <td>' . htmlspecialchars($row['registration_number']) . '</td>
                <td>' . htmlspecialchars($row['status']) . '</td>
                <td>' . htmlspecialchars($row['total_received']) . '</td>
                <td><button>Change Status<button></th>
            </tr>';
    }
    echo '</table>';
}

?>
<h2>Admin - Charities</h2>
<p>TODO: List and manage charities.</p>
<?php include 'footer.php'; ?>
