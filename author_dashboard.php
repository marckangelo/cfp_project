<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Restrict to author users
?>
<h2>Author Dashboard</h2>
<p>TODO: Show author's items and links to create/edit items.</p>

<ul>
    <li><a href="author_item_new.php">Add a new item</a></li>
    <li><a href="author_item_edit.php">Edit an item</a></li>
</ul>

<?php include 'footer.php'; ?>
