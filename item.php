<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Get item_id from GET and load item details, comments, and download button

// LIST OF ITEMS (TEXTS)

// If any successful uploads, show message
if (isset($_SESSION['successful_upload'])) {
    echo '<div style="color: green;">' . $_SESSION['successful_upload'] . '</div>';
    unset($_SESSION['successful_upload']);
}

// If any failed uploads, show message
if (isset($_SESSION['failed_upload'])) {
    echo '<div style="color: red;">' . $_SESSION['failed_upload'] . '</div>';
    unset($_SESSION['failed_upload']);
}

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
                        <th>Keywords</th>
                        <th>Version</th>
                        <th>Upload Date</th>
                        <th>Status</th>
                        <th>Download Count</th>
                        <th>Total Donations ($)</th>
                        <th>Average Rating</th>
                        <th>Comments</th>
                        <th>Action</th>
                    </tr>
            ';

            // Table rows
            while ($row = mysqli_fetch_assoc($result_text_details)) {

                // Fetch keywords for this text
                $keywords = array();

                $text_id = $row['text_id'];

                $sql_text_keywords = "SELECT keyword 
                                    FROM text_keyword 
                                    WHERE text_id = $text_id";

                $result_text_keywords = mysqli_query($conn, $sql_text_keywords);

                while ($row_kw = mysqli_fetch_assoc($result_text_keywords)) {
                    $keywords[] = $row_kw['keyword'];
                }

                $keywords_string = implode(", ", $keywords);



                // *** DELETE THE BUTTON DOESN'T DO ANYTHING YET. IT'S JUST THERE  FOR NOW***
                echo '
                
                    <tr>
                        <td>' . htmlspecialchars($row['title']) . '</td>
                        <td>' . htmlspecialchars($row['abstract']) . '</td>
                        <td>' . htmlspecialchars($row['topic']) . '</td>
                        <td>' . htmlspecialchars($keywords_string) . '</td>
                        <td>' . htmlspecialchars($row['version']) . '</td>
                        <td>' . htmlspecialchars($row['upload_date']) . '</td>
                        <td>' . htmlspecialchars($row['status']) . '</td> 
                        <td>' . htmlspecialchars($row['download_count']) . '</td> 
                        <td>' . htmlspecialchars($row['total_donations']) . '</td>
                        <td>' . htmlspecialchars($row['avg_rating']) . '</td>
                        <td>
                            <p>** TO BE IMPLEMENTED **</p>

                            <p>Format:</p>
                            
                            <div> Comment: <...> </div>
                            <p> Posted by [username]</p>
                        </td>
                        <td>
                            <form method="post" action="download.php">
                                <input type="hidden" name="text_id" value="'. $row['text_id'] . '">
                                <button type="submit" name="download">Download</button>
                            </form>
                            <form method="post" action="donate.php">
                                <input type="hidden" name="text_id" value="'. $row['text_id'] . '">
                                <button type="submit" name="donate">Donate</button>
                            </form>
                            <form method="post" action="author_item_edit.php">
                                <input type="hidden" name="text_id" value="'. htmlspecialchars($row['text_id']) . '">
                                
                                <input type="hidden" name="title" value="'. htmlspecialchars($row['title']) . '">
                                <input type="hidden" name="abstract" value="'. htmlspecialchars($row['abstract']) . '">
                                
                                <input type="hidden" name="topic" value="'. htmlspecialchars($row['topic']) . '">
                                
                                <input type="hidden" name="keywords_string" value="'. htmlspecialchars($keywords_string) . '">
                                <button type="submit" name="edit">Edit</button>
                            </form>
                        </td> 
                    </tr>
                ';
            }
            echo '</table>'; // List of texts table closer tag
        }
    }
} else {
    header("Location: login.php");
    exit;
}

?>
<h2>Item Details</h2>
<p>TODO: Display item details, comments, download button, and donation link.</p>
<?php include 'footer.php'; ?>
