<?php
session_start();
require 'db.php';
// include 'header.php';

// =========== Processing form ==============

// Only run this if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['text_id']) &&
    isset($_POST['title']) &&
    isset($_POST['abstract']) &&
    isset($_POST['topic']) &&
    isset($_POST['keyword'])) {

    // Extract values from form
    $text_id = intval($_POST['text_id']);
    $title = trim($_POST['title']);
    $abstract = trim($_POST['abstract']);
    $topic = trim($_POST['topic']);
    $keyword_string = trim($_POST['keyword']);
    $change_summary = isset($_POST['change_summary']) ? trim($_POST['change_summary']) : "";

    // Safe versions for messages
    $title_sql = mysqli_real_escape_string($conn, $title);

    // 1) Build a simple text payload of the NEW proposed content
    //    We will store this in text_version.changes (no JSON).
    //    Later, admin_items.php will parse these lines using explode() and substr().
    $changes_text  = "TITLE::" . $title . "\n";
    $changes_text .= "ABSTRACT::" . $abstract . "\n";
    $changes_text .= "TOPIC::" . $topic . "\n";
    $changes_text .= "KEYWORDS::" . $keyword_string;

    $changes_sql = mysqli_real_escape_string($conn, $changes_text);

    // 2) Change summary
    if ($change_summary === "") {
        $change_summary = "Author edited text content and/or keywords.";
    }
    $change_summary_sql = mysqli_real_escape_string($conn, $change_summary);

    // 3) Insert a new PENDING version into text_version
    //    NOTE: We are NOT updating the main `text` row or `text_keyword` here.
    $submitted_date = date('Y-m-d');

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

    $result_insert_version = mysqli_query($conn, $sql_insert_version);

    if ($result_insert_version) {
        $_SESSION['successful_update'] =
            "Your edits to '$title_sql' were submitted for moderator review. The public version has not changed yet.";
    } else {
        $_SESSION['failed_update'] =
            "Failed to submit your edits for review. Please try again later.";
    }

    // Go back to the item page (still showing the current live version)
    header("Location: item.php?text_id=" . $text_id);
    exit;
}

// =========== Extracting Form Values (Info of the text before editing them) ==============

// For loading the form the first time, we get text_id via POST (from the Edit button)
if (!isset($_POST['text_id'])) {
    echo "<p style='color:red;'>No text selected for editing.</p>";
    include 'footer.php';
    exit;
}

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
        $text_main_attributes['title'] = $row['title'];
        $text_main_attributes['abstract'] = $row['abstract'];
        $text_main_attributes['topic'] = $row['topic'];
    }

    // Extract all keywords
    $keywords[] = $row['keyword'];
}

// Convert keywords to comma-separated string
$keyword_string = trim(implode(", ", $keywords));
include 'header.php';

?>
<h2>Edit Item</h2>
<p>Edit your item. Your changes will be reviewed by a moderator before they affect the public version.</p>

<form method="post" action="author_item_edit.php">

    <label>Title:
        <input type="text" name="title"
               value="<?php echo htmlspecialchars($text_main_attributes['title']); ?>" required>
    </label><br>

    <label>Abstract:
        <br><textarea name="abstract" required><?php
            echo htmlspecialchars($text_main_attributes['abstract']);
        ?></textarea>
    </label><br>
    
    <label>Topic:
        <input type="text" name="topic"
               value="<?php echo htmlspecialchars($text_main_attributes['topic']); ?>" required>
    </label><br>

    <label>Keyword(s) -- (Must be comma separated):
        <br><textarea name="keyword" required><?php
            echo htmlspecialchars($keyword_string);
        ?></textarea>
    </label><br><br>

    <label>Change summary for moderator (optional):
        <br><textarea name="change_summary" rows="3" cols="60"
                      placeholder="Short summary of what you changed and why."></textarea>
    </label><br><br>
  
    <input type="hidden" name="text_id" value="<?php echo $text_id; ?>">
    <button type="submit">Save Changes</button>
</form>

<?php include 'footer.php'; ?>