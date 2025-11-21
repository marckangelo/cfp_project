<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Implement form to create a new item

// Checking if signed in as author

// Run only if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['orcid'])) {

        // Extract Data:
        $author_orcid = $_SESSION['orcid'];
        $title = trim($_POST['title']);
        $abstract = trim($_POST['abstract']);
        $topic = trim($_POST['topic']);

        // INSERT into table text

        // Build query
        $sql_insert_text = "INSERT INTO text (author_orcid, 
                                            title, 
                                            abstract,
                                            topic,
                                            upload_date)

                                    VALUES ('$author_orcid',
                                            '$title',
                                            '$abstract',
                                            '$topic',
                                            NOW())";

        // Run Query
        $result_text = mysqli_query($conn, $sql_insert_text);


        // Get text_id after inserting into text table
        $text_id = mysqli_insert_id($conn);

        // Split by comma for the keywords.
        $keywords = explode(",", trim($_POST['keyword']));

        // INSERT each keyword into table text_keyword

        $is_valid_keywords = true;
        foreach ($keywords as $k) {

            $k = trim($k);
            if ($k === "") continue; // skip blanks

            // Build query
            $sql_insert_text_keyword = "INSERT INTO text_keyword (text_id, keyword)
                                        VALUES ($text_id, '$k')";

            // Run query
            $result_keyword = mysqli_query($conn, $sql_insert_text_keyword);

            if (!$result_keyword) {
                $is_valid_keywords = false;
                break;
            }
        }

        // Check queries execution
        If ($result_text && $is_valid_keywords) {
            $_SESSION['successful_upload'] = "Text was successfully uploaded!";
            header("Location: item.php");
            exit;
        }

    } else {
        header("Location: login.php");
        exit;
    }
}
?>

<h2>New Item</h2>
<p>TODO: Create item form.</p>
<?php include 'footer.php'; ?>

<form method="post" action="author_item_new.php">

    <label>Title:
        <input type="text" name="title" required>
    </label><br>

    <label>Abstract:
        <br><textarea type="text" name="abstract" required></textarea>
    </label><br>
    
    <label>Topic:
        <input type="text" name="topic" required>
    </label><br>

    <label>Keyword(s) -- (Must be comma separated):
        <br><textarea type="text" name="keywbord" required></textarea>
    </label><br><br>
  
    <button type="submit">UPLOAD</button>
</form>