<?php
session_start();
require 'db.php';
include 'header.php';

//gets the data from the sql table AUTHOR
$query = "SELECT member_id, bio, orcid FROM author";
$result = mysqli_query($conn, $query);

echo "<h2>Authors</h2>";
//displays unorded lists
echo "<ul>";
while ($row = mysqli_fetch_assoc($result)) {
  echo "<li>;
  <a href='author.php?id=" . $author['id'] . "'>" . 
    htmlspecialchars($row['bio']) . 
    "</a> (ORCID: " . 
    htmlspecialchars($row['orcid']) / ")
    </li>";
}
echo "</ul>";

include 'footer.php'; ?>
