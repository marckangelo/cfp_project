<?php
session_start();
require 'db.php';
include 'header.php';

// Show success message from signup page
if (isset($_SESSION['signup_success'])) {
    echo '<div style="color:green;">' . $_SESSION['signup_success'] . '</div>';
    unset($_SESSION['signup_success']);
}

// Collect errors
$errors = array();

// Only run when user submits the login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $primary_email = $_POST['primary_email'];
    $password      = $_POST['password'];

    // Basic validation
    if ($primary_email == "") {
        $errors[] = "Email is required.";
    }

    if ($password == "") {
        $errors[] = "Password is required.";
    }

    // If no errors so far → check login
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

                // SUCCESS → start session data
                $_SESSION['member_id'] = $row['member_id'];
                $_SESSION['name']      = $row['name'];

                // Redirect to index.php (FOR NOW) **** SHOULD BE FIXED ****
                header("Location: index.php");
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
