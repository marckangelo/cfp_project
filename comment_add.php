<?php
session_start();
require 'db.php';

// TODO: Insert a new comment for an item by the logged-in member

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
    $text_id = isset($_POST['text_id']);
    $content = trim($_POST['comment_text']);
    $date = date('Y-m-d');
    $rating = intval($_POST['rating']);
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    if (isset($_POST['parent_comment_id'])) {
        $parent_comment_id = intval($_POST['parent_comment_id']);
    }


    // Validate comment text
    if (empty($content)) {
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
    // If not downloaded, add error
    if (!$has_downloaded) {
        $errors[] = "You must download the text before commenting.";
    }
    // If no errors, insert the comment
    if (empty($errors)) {
        $comment_added = htmlspecialchars($content);

        $sql_insert_comment = "INSERT INTO comment (member_id, text_id, parent_comment_id content, date, rating, is_public)
                               VALUES ($member_id, $text_id, $parent_comment_id, '$comment_added', '$date', $rating, $is_public)";
                        
        $result_insert_comment = mysqli_query($conn, $sql_insert_comment);
        // Redirect back to item page after successful comment
        if ($result_insert_comment) {
            header("Location: item.php?text_id=$text_id");
            exit;
        // display error if insertion fails
        } else {
            $errors[] = "Failed to add comment. Please try again.";
        }
    }

}
?>