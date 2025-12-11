<?php
/*
- Marck Angelo GELI (40265711)
- Arshdeep SINGH (40286514)
- Muhammad Adnan SHAHZAD (40282531)
- Muhammad RAZA (40284058)
*/

/*
Contributor to this file:
- Marck Angelo GELI (40265711)
*/

session_start();
require 'db.php';

// TODO: Admin-only member management (view, change status, etc.)

// Checking if signed in as member **** SHOULD BE CHECKING IF SIGNED IN AS ADMIN ****
if (isset($_SESSION['member_id'])) {
    include 'header.php';

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
                <td>
                    <form action="admin_members_edit.php" method="get">
                        <input type="hidden" name="member_id" value="' . (int)$row['member_id'] . '">
                        <input type="hidden" name="name" value="' . htmlspecialchars($row['name'], ENT_QUOTES) . '">
                        <input type="hidden" name="organization" value="' . htmlspecialchars($row['organization'], ENT_QUOTES) . '">
                        <input type="hidden" name="pseudonym" value="' . htmlspecialchars($row['pseudonym'], ENT_QUOTES) . '">
                        <input type="hidden" name="street" value="' . htmlspecialchars($row['street'], ENT_QUOTES) . '">
                        <input type="hidden" name="city" value="' . htmlspecialchars($row['city'], ENT_QUOTES) . '">
                        <input type="hidden" name="country" value="' . htmlspecialchars($row['country'], ENT_QUOTES) . '">
                        <input type="hidden" name="postal_code" value="' . htmlspecialchars($row['postal_code'], ENT_QUOTES) . '">
                        <input type="hidden" name="primary_email" value="' . htmlspecialchars($row['primary_email'], ENT_QUOTES) . '">
                        <input type="hidden" name="recovery_email" value="' . htmlspecialchars($row['recovery_email'], ENT_QUOTES) . '">
                        <input type="hidden" name="status" value="' . htmlspecialchars($row['status'], ENT_QUOTES) . '">
                        <button type="submit">Edit</button>
                    </form>
                </td>
            </tr>';
    }
    echo '</table>';
} else {
    header("Location: login.php");
}

?>
<h2>Admin - Members</h2>
<p>TODO: List and manage members.</p>
<?php include 'footer.php'; ?>