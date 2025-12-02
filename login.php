<?php
/*
Contributor to this file:
- Muhammad RAZA (40284058)
*/

session_start();
require 'db.php';

// Collect errors
$errors = array();

// Only run when form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $primary_email = trim($_POST['primary_email']);
    $password      = trim($_POST['password']);

    // Basic validation
    if ($primary_email == "") {
        $errors[] = "Email is required.";
    }

    if ($password == "") {
        $errors[] = "Password is required.";
    }

    // If no errors ---> check login
    if (count($errors) == 0) {

        $sql = "SELECT member_id, name, password_hash 
                FROM member
                WHERE primary_email = '$primary_email'
                LIMIT 1";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) == 1) {

            $row = mysqli_fetch_assoc($result);

            // Verify password
            if (password_verify($password, $row['password_hash'])) {

                // SUCCESS → start session
                $_SESSION['member_id'] = $row['member_id'];
                $_SESSION['name']      = $row['name'];
                $member_id = $row['member_id'];

                // Check if Author
                $sql_author = "SELECT orcid FROM author WHERE member_id = $member_id LIMIT 1";
                $result_author = mysqli_query($conn, $sql_author);

                if ($result_author && mysqli_num_rows($result_author) == 1) {
                    $author_row = mysqli_fetch_assoc($result_author);
                    $_SESSION['is_author'] = true;
                    $_SESSION['orcid'] = $author_row['orcid'];
                } else {
                    $_SESSION['is_author'] = false;
                    $_SESSION['orcid'] = null;
                }

                // Check if Admin
                $sql_admin = "SELECT admin_id, role FROM admin WHERE admin_id = $member_id LIMIT 1";
                $result_admin = mysqli_query($conn, $sql_admin);

                if ($result_admin && mysqli_num_rows($result_admin) == 1) {
                    $admin_row = mysqli_fetch_assoc($result_admin);
                    $_SESSION['is_admin']   = true;
                    $_SESSION['admin_id']   = $admin_row['admin_id'];
                    $_SESSION['admin_role'] = $admin_row['role'];
                } else {
                    $_SESSION['is_admin']   = false;
                    $_SESSION['admin_id']   = null;
                    $_SESSION['admin_role'] = null;
                }

                // Check unread messages
                $sql_unread = "
                    SELECT COUNT(*) AS unread_count
                    FROM message
                    WHERE recipient_id = $member_id
                    AND is_read = 0
                ";

                $result_unread = mysqli_query($conn, $sql_unread);

                if ($result_unread) {
                    $row_unread = mysqli_fetch_assoc($result_unread);
                    $_SESSION['has_unread']   = ($row_unread['unread_count'] > 0);
                    $_SESSION['unread_count'] = (int)$row_unread['unread_count'];
                } else {
                    $_SESSION['has_unread']   = false;
                    $_SESSION['unread_count'] = 0;
                }

                unset($_SESSION['unread_alert_shown']); // show popup once per login

                // REDIRECT → matrix verification
                header("Location: matrix_verification.php");
                exit;

            } else {
                $errors[] = "Incorrect password.";
            }

        } else {
            $errors[] = "Account not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="login-box">
    <h2>Login</h2>

    <?php if (count($errors) > 0): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= $e ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php" class="centered-form">

        <label>Email:
            <input type="email" name="primary_email" required>
        </label>

        <label>Password:
            <input type="password" name="password" required>
        </label>

        <button type="submit">Log In</button>
    </form>
</div>

<?php include 'footer.php'; ?>

</body>
</html>