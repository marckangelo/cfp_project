<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Load existing item by ID and allow editing


// =========== Processing form ==============

$text_id = intval($_POST['text_id']);

// Only run this if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST'&&
    isset($_POST['title']) &&
    isset($_POST['abstract']) &&
    isset($_POST['topic']) &&
    isset($_POST['keyword'])) {
    // Extract values from form

    $title    = trim($_POST['title']);
    $abstract = trim($_POST['abstract']);
    $topic    = trim($_POST['topic']);
    $keyword_string = trim($_POST['keyword']);

    // Escapes any character that needs escaping
    $title    = mysqli_real_escape_string($conn, $title);
    $abstract = mysqli_real_escape_string($conn, $abstract);
    $topic    = mysqli_real_escape_string($conn, $topic);

    // Query for Update text values
    $sql_update_text = "
        UPDATE text
        SET
            title    = '$title',
            abstract = '$abstract',
            topic    = '$topic'
        WHERE text_id = $text_id
    ";

    $result_update_text = mysqli_query($conn, $sql_update_text);

    // Query for Delete old keywords for this text
    $sql_delete_keywords = "
        DELETE FROM text_keyword
        WHERE text_id = $text_id
    ";

    $result_delete_keywords = mysqli_query($conn, $sql_delete_keywords);

    // Explode new keywords from textarea
    $keywords = explode(",", $keyword_string);

    $all_keywords_ok = true;

    foreach ($keywords as $k) {

        $k = trim($k);
        if ($k === "") {
            continue; // skip empty bits
        }

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
}


// =========== Extracting Form Values ==============

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
    if(empty($text_main_attributes)) {
        $text_main_attributes['title'] = $row['title'];
        $text_main_attributes['abstract'] = $row['abstract'];
        $text_main_attributes['topic'] = $row['topic'];
    }

    // Extract all keywords
    $keywords[] = $row['keyword'];
}

// Convert keywords to comma-separated string
$keyword_string = trim(implode(", ", $keywords));

?>
<h2>Edit Item</h2>
<p>TODO: Edit item form.</p>

<form method="post" action="author_item_edit.php">

    <label>Title:
        <input type="text" name="title" value="<?php echo $text_main_attributes['title'];?>" required>
    </label><br>

    <label>Abstract:
        <br><textarea name="abstract" required><?php echo $text_main_attributes['abstract'];?></textarea>
    </label><br>
    
    <label>Topic:
        <input type="text" name="topic" value="<?php echo $text_main_attributes['topic'];?>" required>
    </label><br>

    <label>Keyword(s) -- (Must be comma separated):
        <br><textarea name="keyword" required><?php echo $keyword_string;?></textarea>
    </label><br><br>
  
    <button type="submit">Save Changes</button>
</form>

<?php include 'footer.php'; ?>
