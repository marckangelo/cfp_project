<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Restrict to admin users
?>
<h2>Admin Dashboard</h2>
<ul>
    <li><a href="admin_members.php">Manage Members</a></li>
    <li><a href="admin_committees.php">Manage Committees</a></li>
    <li><a href="admin_charities.php">Manage Charities</a></li>
    <li><a href="admin_items.php">Manage Items</a></li>
</ul>
<?php include 'footer.php'; ?>
