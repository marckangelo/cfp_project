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

// If any successful updates, show message
if (isset($_SESSION['successful_update'])) {
    echo '<div style="color: green;">' . $_SESSION['successful_update'] . '</div>';
    unset($_SESSION['successful_update']);
}

// If any failed updates, show message
if (isset($_SESSION['failed_update'])) {
    echo '<div style="color: red;">' . $_SESSION['failed_update'] . '</div>';
    unset($_SESSION['failed_update']);
}

// If any successful donations, show message
if (isset($_SESSION['successful_donation'])) {
    echo '<div style="color: green;">' . $_SESSION['successful_donation'] . '</div>';
    unset($_SESSION['successful_donation']);
}

// If any failed donation, show message
if (isset($_SESSION['failed_donation'])) {
    echo '<div style="color: red;">' . $_SESSION['failed_donation'] . '</div>';
    unset($_SESSION['failed_donation']);
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


                // Retrieving all keywords for this text
                $sql_text_keywords = "SELECT keyword 
                                    FROM text_keyword 
                                    WHERE text_id = $text_id";

                $result_text_keywords = mysqli_query($conn, $sql_text_keywords);

                while ($row_kw = mysqli_fetch_assoc($result_text_keywords)) {
                    $keywords[] = $row_kw['keyword'];
                }

                $keywords_string = implode(", ", $keywords);


                // Retrieving all comments for this text (shows name of member and their comment)
                // Retrieving all comments for this text (shows name of member and their comment)
                $comments       = array();
                $comment_ids    = array();

                $sql_text_comments = "SELECT comment_id, member_id, content 
                                    FROM comment 
                                    WHERE text_id = $text_id";
                $result_text_comments = mysqli_query($conn, $sql_text_comments);

                while ($row_comments = mysqli_fetch_assoc($result_text_comments)) {

                    $comment = $row_comments['content'];

                    // Get member name
                    $sql_member_name = "SELECT name 
                                        FROM member
                                        WHERE member_id = " . (int)$row_comments['member_id'];
                    $result_member_name = mysqli_query($conn, $sql_member_name);
                    $row_member_name    = mysqli_fetch_assoc($result_member_name);
                    $member_name        = $row_member_name['name'];

                    // Save raw text. Escape + add the <br> later
                    $comments[]    = $comment . "\n\n" . "Posted by [$member_name]";
                    $comment_ids[] = (int)$row_comments['comment_id'];
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
                        
                        <td>';

                        // Display the comments + reply button (only for the author of this text)
                        for ($i = 0; $i < count($comments); $i++) {

                            // Show comment text with line breaks
                            echo nl2br(htmlspecialchars($comments[$i])) . '<br>';

                            // If author is logged in AND they are the author of this text, show Reply button
                            if (isset($_SESSION['orcid']) &&
                                isset($row['author_orcid']) &&
                                $_SESSION['orcid'] === $row['author_orcid']) {

                                echo '
                                    <form method="get" action="comment_reply.php" style="display:inline;">
                                        <input type="hidden" name="comment_id" value="' . (int)$comment_ids[$i] . '">
                                        <button type="submit">Reply</button>
                                    </form>
                                ';
                            }

                            echo '<hr>';
                        }

                        echo   '</td>
                        <td>
                            <form method="post" action="download.php">
                                <input type="hidden" name="text_id" value="'. $row['text_id'] . '">
                                <button type="submit" name="download">Download</button>
                            </form>
                            <form method="post" action="donate.php">
                                <input type="hidden" name="text_id" value="'. htmlspecialchars($row['text_id']) . '">
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
