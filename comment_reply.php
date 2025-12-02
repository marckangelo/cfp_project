<?php
session_start();
require 'db.php';
include 'header.php';

// Only authors can reply to comments
if (!isset($_SESSION['orcid'])) {
    header("Location: login.php");
    exit;
}

$author_orcid = mysqli_real_escape_string($conn, $_SESSION['orcid']);
$errors = array();

$original_comment = null;
$original_member_name = "";
$text_id = 0;
$parent_comment_id = 0;
$commentor_id = 0;   // member who wrote the original comment

// ================== PROCESS FORM SUBMISSION ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get hidden fields from form
    $parent_comment_id     = isset($_POST['parent_comment_id']) ? (int) $_POST['parent_comment_id'] : 0;
    $text_id               = isset($_POST['text_id']) ? (int) $_POST['text_id'] : 0;
    $original_member_name  = isset($_POST['original_member_name']) ? $_POST['original_member_name'] : "";
    $commentor_id          = isset($_POST['commentor_id']) ? (int) $_POST['commentor_id'] : 0;
    $reply_text            = isset($_POST['reply_text']) ? trim($_POST['reply_text']) : "";
    $is_public             = isset($_POST['is_public']) ? 1 : 0;

    // Basic validation
    if ($reply_text === "") {
        $errors[] = "Reply text cannot be empty.";
    }

    // Find the author's member_id from the author table
    $sql_author = "
        SELECT member_id
        FROM author
        WHERE orcid = '$author_orcid'
        LIMIT 1
    ";
    $result_author = mysqli_query($conn, $sql_author);

    if (!$result_author || mysqli_num_rows($result_author) === 0) {
        $errors[] = "Author account is not linked to a member.";
    } else {
        $row_author = mysqli_fetch_assoc($result_author);
        $author_member_id = (int) $row_author['member_id'];
    }

    if (empty($errors)) {
        $date = date('Y-m-d');

        // Prefix content so it's clear this is a reply
        $full_content = "Reply to [" . $original_member_name . "]: " . $reply_text;
        $full_content_sql = mysqli_real_escape_string($conn, $full_content);

        // If public reply
        if ($is_public) {
            // ================= REPLYING PUBLICLY TO A COMMENT ================= 

            // Build INSERT reply comment query
            $sql_insert_reply = "
                INSERT INTO comment (member_id, text_id, parent_comment_id, content, date, rating, is_public)
                VALUES ($author_member_id, $text_id, $parent_comment_id, '$full_content_sql', '$date', NULL, 1)
            ";

            $result_insert = mysqli_query($conn, $sql_insert_reply);

            if ($result_insert) {
                $_SESSION['comment_reply_success'] = "Reply posted successfully.";
                $text_id = (int)$text_id;
                header("Location: item.php?id=" . $text_id);
                exit;
            } else {
                $errors[] = "Failed to save reply. Please try again.";
            }

        } else {
            // ================= REPLYING PRIVATELY TO A COMMENT (PRIVATE INBOX) =================

           $date = date('Y-m-d');

            // Get the text title (for the subject line)
            $text_title = "";
            $sql_text_title = "
                SELECT title
                FROM text
                WHERE text_id = $text_id
                LIMIT 1
            ";
            $result_text_title = mysqli_query($conn, $sql_text_title);
            if ($result_text_title && mysqli_num_rows($result_text_title) === 1) {
                $row_text_title = mysqli_fetch_assoc($result_text_title);
                $text_title = $row_text_title['title'];
            }

            // Get the original comment content (to include in the message body)
            $original_comment_body = "";
            $sql_original_comment = "
                SELECT content
                FROM comment
                WHERE comment_id = $parent_comment_id
                LIMIT 1
            ";
            $result_original_comment = mysqli_query($conn, $sql_original_comment);
            if ($result_original_comment && mysqli_num_rows($result_original_comment) === 1) {
                $row_original_comment = mysqli_fetch_assoc($result_original_comment);
                $original_comment_body = $row_original_comment['content'];
            }

            // Build subject: include the text title
            $subject_str = "Reply to your comment on \"" . $text_title . "\"";
            $subject_sql = mysqli_real_escape_string($conn, $subject_str);

            // Build body: include original comment + reply
            $body_str  = "Original comment from [" . $original_member_name . "]:\n";
            $body_str .= $original_comment_body . "\n\n";
            $body_str .= "Reply from author:\n" . $reply_text;

            $body_sql = mysqli_real_escape_string($conn, $body_str);

            // Insert into message table (private inbox)
            $sql_insert = "
                INSERT INTO message (sender_id, recipient_id, subject, body, sent_at, is_read)
                VALUES ($author_member_id, $commentor_id, '$subject_sql', '$body_sql', NOW(), 0)
            ";

            $result_insert = mysqli_query($conn, $sql_insert);

            if ($result_insert) {
                $_SESSION['comment_reply_success'] = "Reply posted successfully.";
                $text_id = (int)$_POST['text_id'];
                header("Location: item.php?text_id=" . $text_id);
                exit;
            } else {
                $errors[] = "Failed to save reply. Please try again.";
            }
        }
    }

    // If there were errors, we fall through and re-load original comment below
    $parent_comment_id = (int) $parent_comment_id;
}

// ================== LOAD ORIGINAL COMMENT (GET or after POST errors) ==================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['comment_id'])) {
        echo "<p>No comment selected to reply to.</p>";
        include 'footer.php';
        exit;
    }

    $parent_comment_id = (int) $_GET['comment_id'];
}

// Load the original comment + commenter name + text_id + commenter member_id
$sql_comment = "
    SELECT c.comment_id,
           c.text_id,
           c.content,
           c.date,
           c.member_id,
           m.name AS member_name
    FROM comment c
    JOIN member m
        ON c.member_id = m.member_id
    WHERE c.comment_id = $parent_comment_id
";
$result_comment = mysqli_query($conn, $sql_comment);

if (!$result_comment || mysqli_num_rows($result_comment) === 0) {
    echo "<p>Comment not found.</p>";
    include 'footer.php';
    exit;
}

$original_comment      = mysqli_fetch_assoc($result_comment);
$text_id               = (int) $original_comment['text_id'];
$original_member_name  = $original_comment['member_name'];
$commentor_id          = (int) $original_comment['member_id'];  // commenter

//Verify this author really owns the text being commented on
$sql_check_author = "
    SELECT 1
    FROM text
    WHERE text_id = $text_id
      AND author_orcid = '$author_orcid'
";
$result_check = mysqli_query($conn, $sql_check_author);

if (!$result_check || mysqli_num_rows($result_check) === 0) {
    echo "<p>You are not the author of this text, so you cannot reply to this comment.</p>";
    include 'footer.php';
    exit;
}

?>

<h2>Reply to Comment</h2>

<?php
// Show errors, if any
if (!empty($errors)) {
    echo '<ul>';
    foreach ($errors as $e) {
        echo '<li><div style="color:red;">' . htmlspecialchars($e) . '</div></li>';
    }
    echo '</ul><br>';
}
?>

<!-- Show the original comment so the author knows what they reply to -->
<div style="border:1px solid #ccc; padding:10px; margin-bottom:15px;">
    <strong>Original comment by <?php echo htmlspecialchars($original_member_name); ?>:</strong><br>
    <em><?php echo nl2br(htmlspecialchars($original_comment['content'])); ?></em><br>
    <small>Posted on <?php echo htmlspecialchars($original_comment['date']); ?></small>
</div>

<form method="post" action="comment_reply.php">

    <label for="reply_text">Your reply:</label><br>
    <textarea id="reply_text" name="reply_text" rows="4" cols="50" required></textarea><br><br>

    <label for="is_public">Make reply public:</label>
    <input type="checkbox" id="is_public" name="is_public" checked><br><br>

    <!-- Hidden fields so POST knows what to reply to -->
    <input type="hidden" name="parent_comment_id" value="<?php echo (int)$original_comment['comment_id']; ?>">
    <input type="hidden" name="text_id" value="<?php echo $text_id; ?>">
    <input type="hidden" name="original_member_name" value="<?php echo htmlspecialchars($original_member_name); ?>">
    <input type="hidden" name="commentor_id" value="<?php echo (int)$commentor_id; ?>">

    <button type="submit">Post Reply</button>
</form>

<?php include 'footer.php'; ?>
