<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CFP Website</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <header>
        <h1>CFP Repository</h1>
        <nav>
            <a href="index.php">Home</a> |
            <a href="browse.php">Browse</a> |
            <a href="authors.php">Authors</a> |
            <a href="statistics.php">Statistics</a> |
            <a href="about.php">About</a>
            <?php if (!empty($_SESSION['member_id'])): ?>
                | <a href="my_account.php">My Account</a>
                | <a href="logout.php">Logout</a>
            <?php else: ?>
                | <a href="login.php">Login</a>
                | <a href="signup.php">Sign Up</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
