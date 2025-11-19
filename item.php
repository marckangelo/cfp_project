<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Get item_id from GET and load item details, comments, and download button

// LIST OF ITEMS (TEXTS)

$sql_text_details = "SELECT * FROM text";

$result_text_details = mysqli_query($conn, $sql_text_details);

if(isset($_SESSION['member_id'])) {
    if ($result_text_details) {
        if (mysqli_num_rows($result_text_details) > 0) {

            // Table header
            echo '
            <h4>List of Items (Texts) </h4>

                <table border="1">
                    <tr>
                        <th>Title</th>
                        <th>Abstract</th>
                        <th>Topic</th>
                        <th>Version</th>
                        <th>Upload Date</th>
                        <th>Status</th>
                        <th>Download Count</th>
                        <th>Total Donations ($)</th>
                        <th>Average Rating</th>
                        <th>Action</th>
                    </tr>
            ';

            // Table rows
            while ($row = mysqli_fetch_assoc($result_text_details)) {

                // *** DELETE THE BUTTON DOESN'T DO ANYTHING YET. IT'S JUST THERE  FOR NOW***
                echo '
                
                    <tr>
                        <td>' . htmlspecialchars($row['title']) . '</td>
                        <td>' . htmlspecialchars($row['abstract']) . '</td>
                        <td>' . htmlspecialchars($row['topic']) . '</td>
                        <td>' . htmlspecialchars($row['version']) . '</td>
                        <td>' . htmlspecialchars($row['upload_date']) . '</td>
                        <td>' . htmlspecialchars($row['status']) . '</td> 
                        <td>' . htmlspecialchars($row['download_count']) . '</td> 
                        <td>' . htmlspecialchars($row['total_donations']) . '</td>
                        <td>' . htmlspecialchars($row['avg_rating']) . '</td>
                        <td>
                            <form method="post" action="download.php">
                                <input type="hidden" name="text_id" value="'. $row['text_id'] . '">
                                <button type="submit" name="download">Download</button>
                            </form>
                        </td> 
                    </tr>
                ';
            }
            echo '</table>'; // List of Committees table closer tag
        }
    }
} else {
    header("Location: login.php");
}

?>
<h2>Item Details</h2>
<p>TODO: Display item details, comments, download button, and donation link.</p>
<?php include 'footer.php'; ?>
