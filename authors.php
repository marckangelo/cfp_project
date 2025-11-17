<?php
session_start();
require 'db.php';
include 'header.php';


$query = "SELECT id, name, orchid FROM authors";
$result = mysqli_query($conn, $query);

echo "<h2>Authors</h2>";
echo "<ul>";
while ($author = mysqli_fetch_assoc($result)) {
  echo "<li>
    <a href='author.php?id=" . $author['id'] . "'>" . 
    htmlspecialchars($author['name']) . 
    "</a> (ORCHID: " . 
    htmlspecialchars($author['orchid'] / ")
    </li>";
}
echo "</ul>";

<?php include 'footer.php'; ?>
