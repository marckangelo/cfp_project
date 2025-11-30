<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Admin-only charities management


// If any successful added charity, show message
if (isset($_SESSION['successful_charity_add'])) {
    echo '<div style="color: green;">' . $_SESSION['successful_charity_add'] . '</div>';
    unset($_SESSION['successful_charity_add']);
}

// If any failed add charity, show message
if (isset($_SESSION['failed_charity_add'])) {
    echo '<div style="color: red;">' . $_SESSION['failed_charity_add'] . '</div>';
    unset($_SESSION['failed_charity_add']);
}

// =============== Charities Table with Action Buttons Display ==================

$sql_charity_details = "SELECT * FROM charity";

$result_charity_details = mysqli_query($conn, $sql_charity_details);

if ($result_charity_details) {
    if (mysqli_num_rows($result_charity_details) > 0) {

        // Table header
        echo '
        <h4>List of Charities</h4>

            <table border="1">
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Mission</th>
                    <th>Country</th>
                    <th>Registration Number</th>
                    <th>Status</th>
                    <th>Total Received ($)</th>
                    <th>Admin Action</th>
                </tr>
        ';

        // Table rows
        while ($row = mysqli_fetch_assoc($result_charity_details)) {

            // Get this row's charity_id that is being sent through form
            $charity_id = (int)$row['charity_id'];

            echo '
                <tr>
                    <td>' . htmlspecialchars($row['name']) . '</td>
                    <td>' . htmlspecialchars($row['description']) . '</td>
                    <td>' . htmlspecialchars($row['mission']) . '</td>
                    <td>' . htmlspecialchars($row['country']) . '</td>
                    <td>' . htmlspecialchars($row['registration_number']) . '</td>
                    <td>' . htmlspecialchars($row['status']) . '</td>
                    <td>' . htmlspecialchars($row['total_received']) . '</td>
                    <td>
                        <form method="get" action="admin_charities_edit.php">
                            <input type="hidden" name="charity_id" value="' . $charity_id . '">
                            <button type="submit" name="edit_charity">Edit</button>
                        </form>

                        <form method="post" action="admin_charities_delete.php"
                        onsubmit="return confirm(\'Are you sure you want to delete this charity? Related donation will also be removed.\');">
                            <input type="hidden" name="charity_id" value="' . $charity_id . '">
                            <button type="submit" name="delete_charity">Delete</button>
                        </form>
                    </td>
                </tr>
            ';
        }
        echo '</table>'; // List of Charities table closer tag
    }
}
?>

<h2>Admin - Charities</h2>
<p>TODO: List and manage charities.</p>
<?php include 'footer.php'; ?>
