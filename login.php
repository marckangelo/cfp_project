<?php
session_start();
require 'db.php';
include 'header.php';

// Collect errors
$errors = array();

// Only run when user submits the login form
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

    // If no errors so far --> check login
    if (count($errors) == 0) {

        // query to get 1 user by email
        $sql = "SELECT member_id, name, password_hash 
                FROM member
                WHERE primary_email = '$primary_email'
                LIMIT 1";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) == 1) {

            $row = mysqli_fetch_assoc($result);

            // Verify password (In PHP, password_verify is the only way to check the password_hash)
            if (password_verify($password, $row['password_hash'])) {

                // SUCCESS --> start session data
                $_SESSION['member_id'] = $row['member_id'];
                $_SESSION['name']      = $row['name'];

                $member_id = $row['member_id'];

                // Is the member an Author?
                $sql_author = "SELECT orcid 
                               FROM author
                               WHERE member_id = $member_id
                               LIMIT 1";

                $result_author = mysqli_query($conn, $sql_author);

                if ($result_author && mysqli_num_rows($result_author) == 1) {
                    $author_row = mysqli_fetch_assoc($result_author);
                    $_SESSION['is_author'] = true;
                    $_SESSION['orcid'] = $author_row['orcid'];
                } else {
                    $_SESSION['is_author'] = false;
                    $_SESSION['orcid'] = null;
                }

                // Is the member an Admin?
                $sql_admin = "SELECT admin_id, role
                            FROM admin
                            WHERE admin_id = $member_id
                            LIMIT 1";

                $result_admin = mysqli_query($conn, $sql_admin);

                if ($result_admin && mysqli_num_rows($result_admin) == 1) {
                    $admin_row = mysqli_fetch_assoc($result_admin);
                    $_SESSION['is_admin']   = true;
                    $_SESSION['admin_id']   = $admin_row['admin_id'];
                    $_SESSION['admin_role'] = $admin_row['role']; // 'super', 'content', or 'financial'
                } else {
                    $_SESSION['is_admin']   = false;
                    $_SESSION['admin_id']   = null;
                    $_SESSION['admin_role'] = null;
                }

                // ================= CHECK FOR UNREAD MESSAGES =================
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

                // Make sure we only show the popup once per login
                unset($_SESSION['unread_alert_shown']);


                // IF login is successful --> Redirect to matrix.php
                header("Location: matrix_verification.php");
                exit;

            } else {
                // Password is wrong
                $errors[] = "Incorrect password.";
            }

        } else {
            // Email not found
            $errors[] = "Account not found.";
        }
    }
}
?>

<h2>Login</h2>

<?php
// Show errors (in red)
if (count($errors) > 0) {
    echo '<div style="color:red;"><ul>';
    foreach ($errors as $e) {
        echo "<li>$e</li>";
    }
    echo '</ul></div>';
}
?>

<form method="post" action="login.php">

    <label>Email:
        <input type="email" name="primary_email" required>
    </label><br>

    <label>Password:
        <input type="password" name="password" required>
    </label><br><br>

    <button type="submit">Log In</button>
</form>

<?php include 'footer.php'; ?>
