<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Load existing item by ID and allow editing

// Extract current values of author text
$text_id = intval($_POST['text_id']);

$sql_author_text = "SELECT t.*, tk.keyword
                    FROM text t
                    JOIN text_keyword tk
                        ON t.text_id = tk.text_id
                    WHERE t.text_id = $text_id
                    ";
$result_author_text = mysqli_query($conn, $sql_author_text);

$keywords = array();

while ($row = mysqli_fetch_assoc($result_author_text)) {
    // Extract all keywords
    $keywords[] = $row['keyword'];
}

// Convert keywords to comma-separated string
$keyword_string = implode(", ", $keywords);

?>
<h2>Edit Item</h2>
<p>TODO: Edit item form.</p>

<form method="post" action="author_item_edit.php">

    <label>Title:
        <input type="text" name="title" value="<?php echo "$row['title']";?>" required>
    </label><br>

    <label>Abstract:
        <br><textarea type="text" name="abstract" required><?php echo "$row['abstract']";?></textarea>
    </label><br>
    
    <label>Topic:
        <input type="text" name="topic" value="<?php echo "$row['topic']";?>" required>
    </label><br>

    <label>Keyword(s) -- (Must be comma separated):
        <br><textarea type="text" name="keyword" required>
            <?php echo $keyword_string;?>
        </textarea>
    </label><br><br>
  
    <button type="submit">Save Changes</button>
</form>

<?php include 'footer.php'; ?>
