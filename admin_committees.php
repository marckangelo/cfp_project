<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Admin-only committees management


// If any successful added committee, show message
if (isset($_SESSION['successful_committee_add'])) {
    echo '<div style="color: green;">' . $_SESSION['successful_committee_add'] . '</div>';
    unset($_SESSION['successful_committee_add']);
}

// If any failed add committee, show message
if (isset($_SESSION['failed_committee_add'])) {
    echo '<div style="color: red;">' . $_SESSION['failed_committee_add'] . '</div>';
    unset($_SESSION['failed_committee_add']);
}

// =============== Committees Table with Action Buttons Display ==================

$sql_committee_details = "SELECT * FROM committee";

$result_committee_details = mysqli_query($conn, $sql_committee_details);

if ($result_committee_details) {
    if (mysqli_num_rows($result_committee_details) > 0) {

        // Table header
        echo '
        <h4>List of Committees</h4>

            <table border="1">
                <tr>
                    <th>Name</th>
                    <th>Purpose</th>
                    <th>Scope</th>
                    <th>Date of Formation</th>
                    <th>Status</th>
                    <th># of Members</th>
                    <th>Admin Action</th>
                </tr>
        ';

        // Table rows
        while ($row = mysqli_fetch_assoc($result_committee_details)) {

            // Get this row's committee_id that is being sent through POST to the form pages
            $committee_id = (int) $row['committee_id'];


            // *** DELETE THE BUTTON DOESN'T DO ANYTHING YET. IT'S JUST THERE  FOR NOW***
            echo '
                <tr>
                    <td>' . htmlspecialchars($row['name']) . '</td>
                    <td>' . htmlspecialchars($row['purpose']) . '</td>
                    <td>' . htmlspecialchars($row['scope']) . '</td>
                    <td>' . htmlspecialchars($row['formation_date']) . '</td>
                    <td>' . htmlspecialchars($row['status']) . '</td> 
                    <td>' . htmlspecialchars($row['member_count']) . '</td> 
                    <td>
                            <form method="get" action="admin_committees_edit.php">
                                <input type="hidden" name="committee_id" value="' . $committee_id . '">
                                <button type="submit" name="edit_committee">Edit</button>
                            </form>

                            <form method="post" action="admin_committees_delete.php"
                            onsubmit="return confirm(\'Are you sure you want to delete this committee?\');">
                                <input type="hidden" name="committee_id" value="' . $committee_id . '">
                                <button type="submit" name="delete_committee">Delete</button>
                            </form>

                    </td>
                </tr>
            ';
        }
        echo '</table>'; // List of Committees table closer tag
    }
}

?>
<h2>Admin - Committees</h2>
<p>TODO: List and manage committees.</p>
<?php include 'footer.php'; ?>