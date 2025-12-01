<?php
session_start();
require 'db.php';
include 'header.php';

// =============== Charities Table Display (Public Listing) ==================

// Only show active charities in this public-style listing
$sql_charity_details = "
    SELECT *
    FROM charity
    WHERE status = 'active'
";

$result_charity_details = mysqli_query($conn, $sql_charity_details);
?>

<h2>Charities</h2>
<p>Below is a list of registered charities available in the CFP system.</p>

<?php
if ($result_charity_details) {
    if (mysqli_num_rows($result_charity_details) > 0) {

        // Table header
        echo '
        <table border="1">
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Mission</th>
                <th>Country</th>
                <th>Registration Number</th>
                <th>Status</th>
                <th>Total Received ($)</th>
                <th>Donations</th>
            </tr>
        ';

        // Table rows
        while ($row = mysqli_fetch_assoc($result_charity_details)) {

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
                        <form method="get" action="donations.php">
                            <input type="hidden" name="charity_id" value="' . $charity_id . '">
                            <button type="submit">View Donations</button>
                        </form>
                    </td>
                </tr>
            ';
        }

        echo '</table>';

    } else {
        echo '<p>No charities found.</p>';
    }
} else {
    echo '<p>Failed to load charities. Please try again later.</p>';
}

include 'footer.php';
?>