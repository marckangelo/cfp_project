<?php
/*
Contributor to this file:
- Marck Angelo Geli (40265711)

*/


session_start();
require 'db.php';
include 'header.php';

// Run only if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['orcid'])) {

        // Extract Data:
        $author_orcid = $_SESSION['orcid'];
        $title        = trim($_POST['title']);
        $abstract     = trim($_POST['abstract']);
        $topic        = trim($_POST['topic']);
        $keyword_raw  = trim($_POST['keyword']);

        // Escape any character that needs escaping
        $title_sql    = mysqli_real_escape_string($conn, $title);
        $abstract_sql = mysqli_real_escape_string($conn, $abstract);
        $topic_sql    = mysqli_real_escape_string($conn, $topic);

        // 1) INSERT into table text
        //    Set explicit initial status + version
        $sql_insert_text = "
            INSERT INTO text (
                author_orcid,
                title,
                abstract,
                topic,
                upload_date,
                status,
                version
            )
            VALUES (
                '$author_orcid',
                '$title_sql',
                '$abstract_sql',
                '$topic_sql',
                NOW(),
                'under_review',  -- new texts go into review pipeline
                1                -- initial version number
            )
        ";

        $result_text = mysqli_query($conn, $sql_insert_text);

        if (!$result_text) {
            $_SESSION['failed_upload'] = "Text failed to upload (text insert error).";
            header("Location: author_item_new.php");
            exit;
        }

        // 2) Get text_id after inserting into text table
        $text_id = mysqli_insert_id($conn);

        // 3) Split by comma for the keywords and insert into text_keyword
        $keywords = explode(",", $keyword_raw);

        $is_valid_keywords = true;
        foreach ($keywords as $k) {

            $k = trim($k);
            if ($k === "") continue; // skip blanks

            $k_sql = mysqli_real_escape_string($conn, $k);

            $sql_insert_text_keyword = "
                INSERT INTO text_keyword (text_id, keyword)
                VALUES ($text_id, '$k_sql')
            ";

            $result_keyword = mysqli_query($conn, $sql_insert_text_keyword);

            if (!$result_keyword) {
                $is_valid_keywords = false;
                break;
            }
        }

        // 4) Create an initial pending version in text_version
        // Submitted content
        $changes_text  = "Initial submission\n";
        $changes_text .= "Title: "    . $title    . "\n";
        $changes_text .= "Abstract: " . $abstract . "\n";
        $changes_text .= "Topic: "    . $topic    . "\n";
        $changes_text .= "Keywords: " . $keyword_raw . "\n";

        $changes_sql        = mysqli_real_escape_string($conn, $changes_text);
        $change_summary     = "Initial submission by author.";
        $change_summary_sql = mysqli_real_escape_string($conn, $change_summary);
        $submitted_date     = date('Y-m-d');

        $sql_insert_version = "
            INSERT INTO text_version (
                text_id,
                changes,
                submitted_date,
                status,
                change_summary,
                moderator_id
            )
            VALUES (
                $text_id,
                '$changes_sql',
                '$submitted_date',
                'pending',
                '$change_summary_sql',
                NULL
            )
        ";

        $result_version = mysqli_query($conn, $sql_insert_version);

        // 5) Final success/failure logic
        if ($result_text && $is_valid_keywords && $result_version) {
            $_SESSION['successful_upload'] =
                "Text was successfully uploaded and submitted for moderator review.";
            header("Location: item.php?text_id=" . $text_id);
            exit;
        } else {
            $_SESSION['failed_upload'] =
                "Text upload or version creation failed. Please try again.";
            header("Location: author_item_new.php");
            exit;
        }

    } else {
        header("Location: login.php");
        exit;
    }
}
?>

<h2>New Item</h2>
<p>Create a new item. It will be submitted for moderator review.</p>

<form method="post" action="author_item_new.php">

    <label>Title:
        <input type="text" name="title" required>
    </label><br>

    <label>Abstract:
        <br><textarea name="abstract" required></textarea>
    </label><br>
    
    <label>Topic:
        <input type="text" name="topic" required>
    </label><br>

    <label>Keyword(s) -- (Must be comma separated):
        <br><textarea name="keyword" required></textarea>
    </label><br><br>
  
    <button type="submit">UPLOAD</button>
</form>

<?php include 'footer.php'; ?>