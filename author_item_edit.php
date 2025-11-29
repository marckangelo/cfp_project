<?php
session_start();
require 'db.php';
include 'header.php';

// =========== Processing form ==============

// Only run this if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['text_id']) &&
    isset($_POST['title']) &&
    isset($_POST['abstract']) &&
    isset($_POST['topic']) &&
    isset($_POST['keyword'])) {

    // Extract values from form
    $text_id        = intval($_POST['text_id']);
    $title          = trim($_POST['title']);
    $abstract       = trim($_POST['abstract']);
    $topic          = trim($_POST['topic']);
    $keyword_string = trim($_POST['keyword']);
    $change_summary = isset($_POST['change_summary']) ? trim($_POST['change_summary']) : "";

    // Escapes any character that needs escaping
    $title_sql    = mysqli_real_escape_string($conn, $title);
    $abstract_sql = mysqli_real_escape_string($conn, $abstract);
    $topic_sql    = mysqli_real_escape_string($conn, $topic);

    // 1) Update main text values (current live version)
    $sql_update_text = "
        UPDATE text
        SET
            title    = '$title_sql',
            abstract = '$abstract_sql',
            topic    = '$topic_sql'
        WHERE text_id = $text_id
    ";

    $result_update_text = mysqli_query($conn, $sql_update_text);

    // 2) Delete old keywords for this text
    $sql_delete_keywords = "
        DELETE FROM text_keyword
        WHERE text_id = $text_id
    ";
    $result_delete_keywords = mysqli_query($conn, $sql_delete_keywords);

    // 3) Insert new keywords
    $keywords = explode(",", $keyword_string);

    $all_keywords_ok = true;
    foreach ($keywords as $k) {

        $k = trim($k);
        if ($k === "") {
            continue; // skip empty bits
        }

        // Escape keyword
        $k_sql = mysqli_real_escape_string($conn, $k);

        $sql_insert_keyword = "
            INSERT INTO text_keyword (text_id, keyword)
            VALUES ($text_id, '$k_sql')
        ";

        $result_kw = mysqli_query($conn, $sql_insert_keyword);

        if (!$result_kw) {
            $all_keywords_ok = false;
            break;
        }
    }

    // 4) Create a new pending version entry in text_version
    // Build a simple "changes" description string
    $changes_text  = "Title: "    . $title . "\n";
    $changes_text .= "Abstract: " . $abstract . "\n";
    $changes_text .= "Topic: "    . $topic . "\n";
    $changes_text .= "Keywords: " . $keyword_string . "\n";

    $changes_sql = mysqli_real_escape_string($conn, $changes_text);

    if ($change_summary === "") {
        $change_summary = "Author edited text content and keywords.";
    }
    $change_summary_sql = mysqli_real_escape_string($conn, $change_summary);

    $submitted_date = date('Y-m-d');

    $sql_insert_version = "
        INSERT INTO text_version (text_id, changes, submitted_date, status, change_summary, moderator_id)
        VALUES ($text_id, '$changes_sql', '$submitted_date', 'pending', '$change_summary_sql', NULL)
    ";

    $result_insert_version = mysqli_query($conn, $sql_insert_version);

    // 5) Decide success / failure message
    if ($result_update_text && $result_delete_keywords && $all_keywords_ok && $result_insert_version) {
        $_SESSION['successful_update'] = "'$title' was successfully updated and submitted for review.";
    } else {
        $_SESSION['failed_update'] = "Failed to update item or create a pending version. Please try again.";
    }

    // Go back to the item page
    header("Location: item.php?text_id=" . $text_id);
    exit;
}

// =========== Extracting Form Values (Info of the text before editing them) ==============

// Extract current values of author text
$text_id = intval($_POST['text_id']);

$sql_author_text = "SELECT t.*, tk.keyword
                    FROM text t
                    JOIN text_keyword tk
                        ON t.text_id = tk.text_id
                    WHERE t.text_id = $text_id
                    ";
$result_author_text = mysqli_query($conn, $sql_author_text);

$text_main_attributes = array();
$keywords = array();

while ($row = mysqli_fetch_assoc($result_author_text)) {
    // Save the main text attributes once
    if (empty($text_main_attributes)) {
        $text_main_attributes['title']    = $row['title'];
        $text_main_attributes['abstract'] = $row['abstract'];
        $text_main_attributes['topic']    = $row['topic'];
    }

    // Extract all keywords
    $keywords[] = $row['keyword'];
}

// Convert keywords to comma-separated string
$keyword_string = trim(implode(", ", $keywords));

?>
<h2>Edit Item</h2>
<p>Edit your item and optionally describe the changes for the moderator.</p>

<form method="post" action="author_item_edit.php">

    <label>Title:
        <input type="text" name="title" value="<?php echo htmlspecialchars($text_main_attributes['title']); ?>" required>
    </label><br>

    <label>Abstract:
        <br><textarea name="abstract" required><?php echo htmlspecialchars($text_main_attributes['abstract']); ?></textarea>
    </label><br>
    
    <label>Topic:
        <input type="text" name="topic" value="<?php echo htmlspecialchars($text_main_attributes['topic']); ?>" required>
    </label><br>

    <label>Keyword(s) -- (Must be comma separated):
        <br><textarea name="keyword" required><?php echo htmlspecialchars($keyword_string); ?></textarea>
    </label><br><br>

    <label>Change summary for moderator (optional):
        <br><textarea name="change_summary" rows="3" cols="60" placeholder="Short summary of what you changed and why."></textarea>
    </label><br><br>
  
    <input type="hidden" name="text_id" value="<?php echo $text_id; ?>">
    <button type="submit">Save Changes</button>
</form>

<?php include 'footer.php'; ?>
