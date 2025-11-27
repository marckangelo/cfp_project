<?php
session_start();
require 'db.php';
include 'header.php';

$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $admin_username = trim($_POST['admin_username']);
    $admin_password = trim($_POST['admin_password']);

    if ($admin_username == "") {
        $errors[] = "Admin username is required.";
    }

    if ($admin_password == "") {
        $errors[] = "Admin password is required.";
    }


    if (count($errors) == 0) {

        $sql = "SELECT admin_id, password  
                FROM admin
                WHERE username = '$admin_id'
                LIMIT 1";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) == 1) {

            $row = mysqli_fetch_assoc($result);

            if ($admin_password === $row['password']) {

                $_SESSION['admin_id'] = $row['admin_id'];
                $_SESSION['username'] = $admin_username;

                header("Location: admin_dashboard.php");
                exit();

            } else {
                $errors[] = "Invalid admin username or password.";
            }

        } else {
            $errors[] = "Invalid admin username or password.";
        }
    }
}
?>

<h2>Admin Login</h2>
<?php
if (count($errors) > 0) {
    echo '<ul style="color: red;">';
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo '</ul>';
}
?>
<form method="post" action="admin_login.php">

    <label>Admin Username:
        <input type="text" name="admin_username" required>
    </label><br>

    <label>Admin Password:
        <input type="password" name="admin_password" required>
    </label><br>

    <input type="submit" value="Login">
</form>
    </label><br>

    <input type="submit" value="Login">
</form>
<?php
include 'footer.php';
?>