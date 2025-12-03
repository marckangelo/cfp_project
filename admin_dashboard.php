<?php
session_start();
require 'db.php';
// include 'header.php';

// TODO: Restrict to admin users
if (empty($_SESSION['is_admin']) || empty($_SESSION['admin_role'])) {
    header("Location: login.php");
    exit;
}

$admin_role = $_SESSION['admin_role']; // 'super', 'content', or 'financial'
include 'header.php';
?>

<div class="dashboard-container">
    <h2 class="centered-title">Admin Dashboard</h2>

    <ul class="dashboard-links">
        <?php if ($admin_role === 'super' || $admin_role === 'content'): ?>
            <li><a href="admin_members.php">Manage Members</a></li>
            <li><a href="admin_items.php">Manage Items</a></li>
        <?php endif; ?>

        <?php if ($admin_role === 'super' || $admin_role === 'financial'): ?>
            <li><a href="admin_charities.php">Manage Charities</a></li>
        <?php endif; ?>

        <?php if ($admin_role === 'super'): ?>
            <li><a href="admin_committees.php">Manage Committees</a></li>
        <?php endif; ?>
    </ul>
</div>

<?php include 'footer.php'; ?>
