<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Admin-only member management (view, change status, etc.)

// Checking if signed in as member **** SHOULD BE CHECKING IF SIGNED IN AS ADMIN ****
if (isset($_SESSION['member_id'])) {
    // DISPLAY MEMBER DETAILS
    $sql_author_details = "SELECT 
                          a.orcid,
                          m.name,
                          m.organization,
                          m.primary_email,
                          a.specialization,
                          a.h_index,
                          a.total_downloads
                          FROM author a
                          JOIN member m ON a.member_id = m.member_id;
                          ";
    
    // Run the query
    $result_author_details = mysqli_query($conn, $sql_author_details);

    // Table header
    echo '
    <h4>Member Details</h4>

        <table border="1">
            <tr>
                <th>Orcid</th>
                <th>Name</th>
                <th>Organization</th>
                <th>Primary Email</th>
                <th>Specialization</th>
                <th>h index</th>
                <th>Total Downloads</th>
            </tr>
    ';
    
    //Table rows (Fetching each row from member detail using while loop)
    while ($row = mysqli_fetch_assoc($result_author_details)) {
        echo '
            <tr>
                <td>' . htmlspecialchars($row['orcid']) . '</td>
                <td>' . htmlspecialchars($row['name']) . '</td>
                <td>' . htmlspecialchars($row['organization']) . '</td>
                <td>' . htmlspecialchars($row['primary_email']) . '</td>
                <td>' . htmlspecialchars($row['specialization']) . '</td>    
                <td>' . htmlspecialchars($row['h_index']) . '</td>
                <td>' . htmlspecialchars($row['total_downloads']) . '</td>
            </tr>';
    }
    echo '</table>';
}

?>
<h2>Authors</h2>
<p>TODO: List authors.</p>
<?php include 'footer.php'; ?>
