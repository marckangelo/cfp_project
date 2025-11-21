<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Implement form to create a new item

// Checking if signed in as author
if (isset($_SESSION['orcid'])) {

// Extract Data:
$author_orcid = $_SESSION['orcid'];
$title = $_POST['title'];
$abstract = $_POST['abstract'];
$topic = $_POST['topic'];
$keyword = $_POST['keyword'];

// INSERT into table text    ***** INCOMPLETE *******
$sql_insert_text = "INSERT INTO text (author_orcid, 
                                      title, 
                                      abstract,
                                      topic,
                                      version,
                                      upload_date)

                              VALUES ($author_orcid,
                                      $title,
                                      $abstract,
                                      $topic,
                                      $version,
                                      NOW())";


} else {
    header("Location: login.php");
    exit;
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

    <label>Keyword:
        <br><textarea type="text" name="keywbord" required></textarea>
    </label><br><br>
  
    <button type="submit">UPLOAD</button>
</form>