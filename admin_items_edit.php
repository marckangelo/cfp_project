<?php
session_start();
require 'db.php';
/*
- Marck Angelo GELI (40265711)
- Arshdeep SINGH (40286514)
- Muhammad Adnan SHAHZAD (40282531)
- Muhammad RAZA (40284058)
*/

/*
Contributor to this file:
- Arshdeep SINGH (40286514)
*/

// ========== Enforce admin with 'content' or 'super' role ==========
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

$admin_role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : null;
if ($admin_role !== 'super' && $admin_role !== 'content') {
    echo "<p>You do not have permission to edit items.</p>";
    include 'footer.php';
    exit;
}

// =========== Processing form (POST) ==============
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['text_id']) &&
    isset($_POST['title']) &&
    isset($_POST['abstract']) &&
    isset($_POST['topic']) &&
    isset($_POST['status']) &&
    isset($_POST['keyword'])) {

    // Extract values from form
    $text_id = (int) $_POST['text_id'];
    $title = trim($_POST['title']);
    $abstract = trim($_POST['abstract']);
    $topic = trim($_POST['topic']);
    $status = trim($_POST['status']);
    $keyword_string = trim($_POST['keyword']);

    // Escape for safety
    $title_sql = mysqli_real_escape_string($conn, $title);
    $abstract_sql = mysqli_real_escape_string($conn, $abstract);
    $topic_sql = mysqli_real_escape_string($conn, $topic);
    $status_sql = mysqli_real_escape_string($conn, $status);

    // 1) Update the main text row directly
    $sql_update_text = "
        UPDATE text
        SET
            title = '$title_sql',
            abstract = '$abstract_sql',
            topic = '$topic_sql',
            status = '$status_sql'
        WHERE text_id = $text_id
    ";

    $result_update_text = mysqli_query($conn, $sql_update_text);

    if ($result_update_text) {

        // 2) Update keywords: remove old ones and re-insert from the textarea
        $sql_delete_keywords = "
            DELETE FROM text_keyword
            WHERE text_id = $text_id
        ";
        $result_delete = mysqli_query($conn, $sql_delete_keywords);

        if ($result_delete) {
            // Split comma-separated keywords and insert each
            $keywords = explode(",", $keyword_string);

            foreach ($keywords as $k) {
                $k = trim($k);
                if ($k === "") {
                    continue;
                }
                $k_sql = mysqli_real_escape_string($conn, $k);

                $sql_insert_keyword = "
                    INSERT INTO text_keyword (text_id, keyword)
                    VALUES ($text_id, '$k_sql')
                ";
                mysqli_query($conn, $sql_insert_keyword);
            }
        }

        // Success message for admin_items.php
        $_SESSION['admin_item_success'] = "Item #$text_id has been updated successfully.";
        header("Location: admin_items.php");
        exit;

    } else {
        echo "<div style='color:red;'>Failed to update the item. Please try again.</div>";
    }
}

// =========== Loading the form the first time (GET) ==============

// Expect text_id via GET (from Edit button on admin_items.php)
if (!isset($_GET['text_id'])) {
    echo "<p style='color:red;'>No item selected for editing.</p>";
    include 'footer.php';
    exit;
}

$text_id = (int) $_GET['text_id'];

// Load text + its keywords
$sql_text = "
    SELECT t.*, tk.keyword
    FROM text t
    LEFT JOIN text_keyword tk
        ON t.text_id = tk.text_id
    WHERE t.text_id = $text_id
";

$result_text = mysqli_query($conn, $sql_text);

if (!$result_text || mysqli_num_rows($result_text) === 0) {
    echo "<p style='color:red;'>Item not found.</p>";
    include 'footer.php';
    exit;
}

// Collect main attributes and keywords (basically same as author_item_edit.php)
$text_main_attributes = array();
$keywords = array();

while ($row = mysqli_fetch_assoc($result_text)) {

    if (empty($text_main_attributes)) {
        $text_main_attributes['title'] = $row['title'];
        $text_main_attributes['abstract'] = $row['abstract'];
        $text_main_attributes['topic'] = $row['topic'];
        $text_main_attributes['status'] = $row['status'];
    }

    if (!empty($row['keyword'])) {
        $keywords[] = $row['keyword'];
    }
}

// Build a comma-separated keyword string
$keyword_string = trim(implode(", ", $keywords));
include 'header.php';
?>
<h2>Admin - Edit Item</h2>
<p>Edit this item. Your changes take effect immediately (no moderation step).</p>

<form method="post" action="admin_items_edit.php">

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

    <label>Status:
        <select name="status">
            <option value="draft" <?php if ($text_main_attributes['status'] === 'draft') echo 'selected'; ?>>draft</option>
            <option value="under_review" <?php if ($text_main_attributes['status'] === 'under_review') echo 'selected'; ?>>under_review</option>
            <option value="published" <?php if ($text_main_attributes['status'] === 'published') echo 'selected'; ?>>published</option>
            <option value="archived" <?php if ($text_main_attributes['status'] === 'archived') echo 'selected'; ?>>archived</option>
        </select>
    </label><br>

    <label>Keyword(s) -- (Must be comma separated):
        <br><textarea name="keyword" required><?php
            echo htmlspecialchars($keyword_string);
        ?></textarea>
    </label><br><br>

    <input type="hidden" name="text_id" value="<?php echo $text_id; ?>">
    <button type="submit">Save Changes</button>
    <a href="admin_items.php"><button type="button">Cancel</button></a>
</form>

<?php include 'footer.php'; ?>