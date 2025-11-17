<?php
session_start();
require 'db.php';
include 'header.php';

//gets the data from the sql table AUTHOR
$query = "SELECT id, name, orcid FROM authors";
$result = mysqli_query($conn, $query);

echo "<h2>Authors</h2>";
//displays unorded lists
echo "<ul>";
while ($row = mysqli_fetch_assoc($result)) {
  echo "<li>;
  <a href='author.php?id=" . $author['id'] . "'>" . 
    htmlspecialchars($row['name']) . 
    "</a> (ORCID: " . 
    htmlspecialchars($row['orcid'] / ")
    </li>";
}
echo "</ul>";

<?php include 'footer.php'; ?>
