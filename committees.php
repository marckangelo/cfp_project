<?php
session_start();
require 'db.php';
include 'header.php';


//DISPLAY LIST OF COMMITTEES
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
                    <th>Action</th>
                </tr>
        ';

        // Table rows
        while ($row = mysqli_fetch_assoc($result_committee_details)) {

            // *** THE JOIN BUTTON DOESN'T DO ANYTHING YET. IT'S JUST THERE  FOR NOW***
            echo '
                <tr>
                    <td>' . htmlspecialchars($row['name']) . '</td>
                    <td>' . htmlspecialchars($row['purpose']) . '</td>
                    <td>' . htmlspecialchars($row['scope']) . '</td>
                    <td>' . htmlspecialchars($row['formation_date']) . '</td>
                    <td>' . htmlspecialchars($row['status']) . '</td> 
                    <td>' . htmlspecialchars($row['member_count']) . '</td> 
                    <td>
                        <form action="committee_request.php" method="POST">
                            <input type="hidden" name="committee_id" value="' . $row['committee_id'] . '">
                            <input type="hidden" name="status" value="' . $row['status'] . '">
                            <button type="submit">Join</button>
                        </form>
                    </td>
                </tr>
            ';
        }
        echo '</table>'; // List of Committees table closer tag
    }
}
//displays message 
else {
    echo "<p> No committees available. </p>";
}

include 'footer.php'; ?>
