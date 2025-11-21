<?php
session_start();
require 'db.php';

// TODO: Insert a new comment for an item by the logged-in member
/*
Each contributing member(author) has a home page which has an index of his/her contents. The
number of downloads, the to-date-contributions made for the title. List of other text that were
derived new versions of each of the authors text.. Any CFP members who had downloaded a title
by the author could add comments about the title. The author could respond to such comments
and may update, errors etc, in the title if required. Such updated version is checked by a CFP
moderator and would replace the original text.
*/
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}
$member_id = $_SESSION['member_id'];
$errors = [];

$downloads_by_member = [];
$downloads_by_member_sql = "SELECT * FROM download WHERE member_id = $member_id";
$downloads_by_member_result = mysqli_query($conn, $downloads_by_member_sql);

if ($downloads_by_member_result) {
    while ($row = mysqli_fetch_assoc($downloads_by_member_result)) {
        $downloads_by_member[] = $row;
    }
} 
// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text_id = $_POST['text_id'];
    $content = trim($_POST['comment_text']);
    $date = date('Y-m-d');
    $rating = intval($_POST['rating']);
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    // Validate comment text
    if (empty($$content)) {
        $errors[] = "Comment text cannot be empty.";
    }

    if ($rating < 1 || $rating > 5) {
        $errors[] = "Rating must be between 1 and 5.";
    }
    // Check if member has downloaded the text before allowing comment
    $has_downloaded = false;
    foreach ($downloads_by_member as $download) {
        if ($download['text_id'] == $text_id) {
            $has_downloaded = true;
            break;
        }
    }
    if (!$has_downloaded) {
        $errors[] = "You must download the text before commenting.";
    }
    // If no errors, insert the comment
    if (empty($errors)) {
        $sql_insert_comment = "INSERT INTO comments (member_id, text_id, content, date, rating, is_public)
                               VALUES ($member_id, $text_id, ?, '$date', $rating, $is_public)";
        $stmt = mysqli_prepare($conn, $sql_insert_comment);
        mysqli_stmt_bind_param($stmt, 's', $content);
        $result_insert = mysqli_stmt_execute($stmt);

        if ($result_insert) {
            header("Location: item.php?text_id=$text_id");
            exit;
        } else {
            $errors[] = "Failed to add comment. Please try again.";
        }
    }
    
}

?>
