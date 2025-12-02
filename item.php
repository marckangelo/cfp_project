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
   
// Check for a specific text_id coming from browse.php
if (!isset($_GET['text_id'])) {
    echo "<p style='color:red;'>No text selected.</p>";
    include 'footer.php';
    exit;
}

$text_id = (int) $_GET['text_id'];

$sql_text_details = "SELECT * FROM text WHERE text_id = $text_id";

$result_text_details = mysqli_query($conn, $sql_text_details);

// Default values for plagiarism committee flags (for guests or non-committee members)
$is_plag_committee_member = false;
$committee_id_for_user    = null;

if(isset($_SESSION['member_id'])) {

    // Check if the logged-in member is part of an active plagiarism committee

    $member_id = (int) $_SESSION['member_id'];

    $sql_plag_committee = "
        SELECT c.committee_id
        FROM committee_membership cm
        JOIN committee c
            ON cm.committee_id = c.committee_id
        WHERE cm.member_id = $member_id
          AND cm.status = 'active'
          AND c.status = 'active'
          AND c.scope = 'plagiarism'
        LIMIT 1
    ";

    $result_plag_committee = mysqli_query($conn, $sql_plag_committee);

    if ($result_plag_committee && mysqli_num_rows($result_plag_committee) > 0) {
        $row_plag = mysqli_fetch_assoc($result_plag_committee);
        $is_plag_committee_member = true;
        $committee_id_for_user = (int)$row_plag['committee_id'];
    }
}

if ($result_text_details) {
    if (mysqli_num_rows($result_text_details) > 0) {

        // Get first row to know the status of this text
        $first_row = mysqli_fetch_assoc($result_text_details);
        $show_actions_column = ($first_row['status'] !== 'under_review');

        // Put all rows (first + remaining) into an array for processing
        $rows = array();
        $rows[] = $first_row;
        while ($row_tmp = mysqli_fetch_assoc($result_text_details)) {
            $rows[] = $row_tmp;
        }

        // Table header
        echo '
        <h2>Item Details</h2>

            <table border="1">
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Abstract</th>
                    <th>Topic</th>
                    <th>Keywords</th>
                    <th>Version</th>
                    <th>Upload Date</th>
                    <th>Status</th>
                    <th>Download Count</th>
                    <th>Total Donations ($)</th>
                    <th>Average Rating</th>
                    <th>Comments</th>';
                    
        if ($show_actions_column) {
            echo '<th>Action</th>';
        }

        echo '
                </tr>
        ';

        // Table rows
        foreach ($rows as $row) {

            // Fetch keywords for this text
            $keywords = array();

            $text_id = (int)$row['text_id'];

            // Fetch author name
            $author_name = "-";
            if (!empty($row['author_orcid'])) {

                $author_orcid_sql = mysqli_real_escape_string($conn, $row['author_orcid']);

                $sql_author_name = "
                    SELECT m.name
                    FROM author a
                    JOIN member m
                        ON a.member_id = m.member_id
                    WHERE a.orcid = '$author_orcid_sql'
                    LIMIT 1
                ";

                $result_author_name = mysqli_query($conn, $sql_author_name);

                if ($result_author_name && mysqli_num_rows($result_author_name) > 0) {
                    $row_author_name = mysqli_fetch_assoc($result_author_name);
                    $author_name = $row_author_name['name'];
                }
            }


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
            $comments = array();
            $comment_ids = array();

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
                $row_member_name = mysqli_fetch_assoc($result_member_name);
                $member_name = $row_member_name['name'];

                // Save raw text. Escape + add the <br> later
                $comments[]    = $comment . "\n\n" . "Posted by [$member_name]";
                $comment_ids[] = (int)$row_comments['comment_id'];
            }

            $keywords_string = implode(", ", $keywords);

            // *** DELETE THE BUTTON DOESN'T DO ANYTHING YET. IT'S JUST THERE  FOR NOW***
            echo '
            
                <tr>
                    <td>' . htmlspecialchars($row['title']) . '</td>
                    <td>' . htmlspecialchars($author_name) . '</td>
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
                                    <input type="hidden" name="text_id" value="' . (int)$text_id . '">
                                    <button type="submit">Reply</button>
                                </form>
                            ';
                        }

                        echo '<hr>';
                    }

                    echo   '</td>';
                    
                    if ($show_actions_column) {

                        echo '
                    <td>
                        <form method="post" action="download.php">
                            <input type="hidden" name="text_id" value="'. $row['text_id'] . '">
                            <button type="submit" name="download">Download</button>
                        </form>
                        <form method="post" action="donate.php">
                            <input type="hidden" name="text_id" value="'. htmlspecialchars($row['text_id']) . '">
                            <button type="submit" name="donate">Donate</button>
                        </form>
                    ';
                        
                        if (!empty($_SESSION['orcid']) && $_SESSION['orcid'] === $row['author_orcid']) {
                          echo '<form method="post" action="author_item_edit.php">
                                    <input type="hidden" name="text_id" value="'. htmlspecialchars($row['text_id']) . '">
                                    
                                    <input type="hidden" name="title" value="'. htmlspecialchars($row['title']) . '">
                                    <input type="hidden" name="abstract" value="'. htmlspecialchars($row['abstract']) . '">
                                    
                                    <input type="hidden" name="topic" value="'. htmlspecialchars($row['topic']) . '">
                                    
                                    <input type="hidden" name="keywords_string" value="'. htmlspecialchars($keywords_string) . '">
                                    <button type="submit" name="edit">Edit</button>
                                </form>
                            ';
                        }

                        // If the logged-in member is in an active plagiarism committee,
                        // show a button to open a plagiarism case for this text
                        if ($is_plag_committee_member && $committee_id_for_user !== null) {
                            echo '
                            <form method="get" action="plagiarism_case_open.php">
                                <input type="hidden" name="text_id" value="'. (int)$row['text_id'] . '">
                                <input type="hidden" name="committee_id" value="'. (int)$committee_id_for_user . '">
                                <button type="submit" name="open_plagiarism_case">Open Plagiarism Case</button>
                            </form>
                            ';
                        }

                        echo '
                    </td> 
                ';
                    }

            echo '
                </tr>
            ';
        }
        echo '</table>'; // List of texts table closer tag
    }
}

?>
<?php include 'footer.php'; ?>