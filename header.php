<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = !empty($_SESSION['member_id']);
$is_author    = !empty($_SESSION['orcid']);
$is_admin = (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true)
            || !empty($_SESSION['admin_id']);
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
            <a href="committees.php">Committees</a> |
            <a href="statistics.php">Statistics</a> |
            <a href="about.php">About</a> |

            <?php if ($is_logged_in): ?>
                <a href="messages_inbox.php">Inbox</a> |

                <?php if ($is_author): ?>
                    <a href="author_dashboard.php">Author Dashboard</a> |
                <?php endif; ?>

                <?php if ($is_admin): ?>
                    <!-- Dev mode: admin can see this; later you restrict admin_*.php internally -->
                    <a href="admin_dashboard.php">Admin Dashboard</a> |
                <?php endif; ?>

                <a href="my_account.php">My Account</a> |
                <a href="logout.php">Logout</a> |
            <?php else: ?>
                <a href="login.php">Login</a> |
                <a href="signup.php">Sign Up</a> |
            <?php endif; ?>
        </nav>
    </header>
    <main>
