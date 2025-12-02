<?php
session_start();
require 'db.php';


if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}
$member_id = (int) $_SESSION['member_id'];
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text_id = isset($_POST['text_id']) ? intval($_POST['text_id']) : 0;
    $content = isset($_POST['comment_text']) ? $_POST['comment_text'] : '';
    $date = date('Y-m-d');
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    if (isset($_POST['parent_comment_id']) && !empty($_POST['parent_comment_id'])) {
        $parent_comment_id = intval($_POST['parent_comment_id']);
        $parent_comment_id_value = $parent_comment_id;
    }
    else {
        $parent_comment_id_value = 'NULL';
    }

    if ($rating < 1 || $rating > 5) {
        $errors[] = "Rating must be between 1 and 5.";
    }

    // Validate comment text
    if (empty($content)) {
        $errors[] = "Comment text cannot be empty.";
    }

    // Check if member has downloaded the text before allowing comment
    $has_downloaded = false;

    $sql_download_check = "
        SELECT download_id
        FROM download
        WHERE member_id = $member_id
          AND text_id = $text_id
        LIMIT 1
    ";

    $result_download_check = mysqli_query($conn, $sql_download_check);

    if ($result_download_check && mysqli_num_rows($result_download_check) > 0) {
        $has_downloaded = true;
    }

    // If not downloaded, add error
    if (!$has_downloaded) {
        $errors[] = "You must download the text before commenting.";
    }

    // If no errors, insert the comment
    if (empty($errors)) {
        $comment_added = addslashes($content);

        $sql_insert_comment = "INSERT INTO comment (member_id, text_id, parent_comment_id, content, date, rating, is_public)
                               VALUES ($member_id, $text_id, $parent_comment_id_value, '$comment_added', '$date', $rating, $is_public)";
                        
        $result_insert_comment = mysqli_query($conn, $sql_insert_comment);
        // Redirect back to item page after successful comment
        if ($result_insert_comment) {

            // ===== Recalculate and update avg_rating for this text =====
            $sql_avg = "
                SELECT AVG(rating) AS avg_rating
                FROM comment
                WHERE text_id = $text_id
                  AND rating IS NOT NULL
            ";
            $result_avg = mysqli_query($conn, $sql_avg);

            if ($result_avg) {
                $row_avg = mysqli_fetch_assoc($result_avg);
                if ($row_avg && $row_avg['avg_rating'] !== null) {
                    $avg_rating = round((float)$row_avg['avg_rating'], 2);

                    $sql_update_avg = "
                        UPDATE text
                        SET avg_rating = $avg_rating
                        WHERE text_id = $text_id
                    ";
                    mysqli_query($conn, $sql_update_avg);
                }
            }
            // ===== END avg_rating recalculation =====

            header("Location: my_account.php");
            exit;
        // display error if insertion fails
        } else {
            $errors[] = "Failed to add comment. Please try again.";
        }
    }

    // Diplay errors if any (in red)
    if (!empty($errors)) {
        echo 
            '<ul>';
                foreach ($errors as $e) {
                    echo '<li><div style="color:red;">' . $e . '</div></li>';
                }
            echo '</ul>';
        echo '<br>';
    }
}

// Figure out which text we are commenting on (from GET first, then POST)
if (isset($_GET['text_id'])) {
    $text_id_for_form = (int) $_GET['text_id'];
} elseif (isset($_POST['text_id'])) {
    $text_id_for_form = (int) $_POST['text_id'];
} else {
    echo "<p style='color:red;'>No text selected for commenting.</p>";
    exit;
}

// Fetch the title of this text for display
$sql_text = "SELECT title FROM text WHERE text_id = $text_id_for_form";
$result_text = mysqli_query($conn, $sql_text);

if (!$result_text || mysqli_num_rows($result_text) === 0) {
    echo "<p style='color:red;'>Selected text not found.</p>";
    exit;
}

$row_text = mysqli_fetch_assoc($result_text);
$text_title = $row_text['title'];
?>

<form action="comment_add.php" method="post">

    <p><strong>Text:</strong> <?php echo htmlspecialchars($text_title); ?></p>
    <input type="hidden" name="text_id" value="<?php echo $text_id_for_form; ?>">

    <?php if (isset($_GET['ReplyTo'])): ?>
        <input type="hidden" name="parent_comment_id" value="<?php echo intval($_GET['ReplyTo']); ?>">
    <?php endif; ?>

    <label for="comment_text">Comment:</label><br>
    <textarea name="comment_text" id="comment_text" rows="4" cols="50" required></textarea><br>

    <label for="rating">Rating (1-5):</label><br>
    <label>
        <input type="radio" name="rating" value="1" required>1 - Poor
    </label><br>

    <label>
        <input type="radio" name="rating" value="2">2 - Adequate
    </label><br>

    <label>
        <input type="radio" name="rating" value="3">3 - Decent
    </label><br>

    <label>
        <input type="radio" name="rating" value="4">4 - Good
    </label><br>

    <label>
        <input type="radio" name="rating" value="5">5 - Excellent
    </label><br><br>

    <label for="is_public">Make comment public:</label>
    <input type="checkbox" name="is_public" id="is_public" checked><br>

    <input type="submit" value="Add Comment">
</form>