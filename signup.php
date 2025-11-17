<?php
session_start();
require 'db.php';
include 'header.php';

// Array to keep error messages
$errors = array();
$success = "";

// Only run this if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Get values from the form
    $name = $_POST['name'];
    $organization = $_POST['organization'];
    $primary_email = $_POST['primary_email'];
    $recovery_email = $_POST['recovery_email'];
    $password = $_POST['password'];

    $street = $_POST['street'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $country = $_POST['country'];
    $postal_code = $_POST['postal_code'];

    $introduced_by = $_POST['introduced_by'];
    $pseudonym = $_POST['pseudonym'];

    // 2. Basic validation (very simple)

    // Name required
    if ($name == "") {
        $errors[] = "Name is required.";
    }

    // Primary email required
    if ($primary_email == "") {
        $errors[] = "Primary email is required.";
    } else {
        // super basic email check: must contain format like alphanumeric@domain.com
        if (!preg_match("/^[a-zA-Z0-9]+@[a-zA-Z0-9\.]+\.(com|ca|org)$/", $primary_email)) {
            $errors[] = "Primary email is not valid. Use format like: alphanumeric@domain.com (example: user@cs.concordia.ca).";
        }

        // other version of email check (checks for common domains like gmail, yahoo or outlook):
        // if (!preg_match("/^[a-zA-Z0-9]+@(gmail|yahoo|outlook)\.(com|ca)$/", $primary_email)) {
        //     $errors[] = "Email must be gmail, yahoo, or outlook (.com or .ca).";
        // }
    }

    // Optional recovery email: if provided, do the same simple check
    // super basic email check: must contain format like alphanumeric@domain.com
     if ($recovery_email != "") {
        if (!preg_match("/^[a-zA-Z0-9]+@[a-zA-Z0-9\.]+\.(com|ca|org)$/", $recovery_email)) {
            $errors[] = "Primary email is not valid. Use format like: alphanumeric@domain.com (example: user@cs.concordia.ca).";
        }
        elseif ($recovery_email == $primary_email) {
            $errors[] = "Recovery Email cannot be the same as the Primary email.";
        }
    }
        

    // Password length check (basic)
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    // 3. If no validation errors so far, check if email already exists
    if (count($errors) == 0) {

        // get all emails and compare in PHP
        $sql = "SELECT primary_email FROM member";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $email_exists = false;

            while ($row = mysqli_fetch_assoc($result)) {
                if ($row['primary_email'] == $primary_email) {
                    $email_exists = true;
                    break;
                }
            }

            if ($email_exists) {
                $errors[] = "This email is already registered.";
            }
        } else {
            $errors[] = "Database error while checking email.";
        }
    }

    // 4. If still no errors, insert into database
    if (count($errors) == 0) {

        // Hash the password (one function, you can think of it as "encrypt")
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Simple values
        $join_date = date("Y-m-d");
        $status = "active";

        // Verification matrix: random 16-character string
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $verification_matrix = "";

        // loop where it concatenates 1 random character 16 times to create the  verification_matrix
        for ($i = 0; $i < 16; $i++) {
            $verification_matrix .= $chars[rand(0, strlen($chars) - 1)];
        }

        // Expiry date: 30 days from now (can still be changed --> **30 days only FOR NOW**)
        $matrix_expiry_date = date("Y-m-d", strtotime("+30 days"));

        $sql = "INSERT INTO member
                (name, organization, primary_email, recovery_email, password_hash, join_date, status,
                street, city, state, country, postal_code,
                introduced_by, pseudonym,
                verification_matrix, matrix_expiry_date)
                VALUES
                ('$name', '$organization', '$primary_email', '$recovery_email', '$password_hash', '$join_date', '$status',
                '$street', '$city', '$state', '$country', '$postal_code',
                '$introduced_by', '$pseudonym',
                '$verification_matrix', '$matrix_expiry_date')";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['signup_success'] = "Account created successfully!";
            header("Location: login.php");
            exit;
        }
    }
}
?>

<h2>Sign Up</h2>

<?php
// Show errors if there are any (IN RED)
if (count($errors) > 0) {
    echo '<div style="color:red;"><ul>';
    for ($i = 0; $i < count($errors); $i++) {
        echo '<li>' . $errors[$i] . '</li>';
    }
    echo '</ul></div>';
}

// Show success message if any (IN GREEN)
if ($success != "") {
    echo '<div style="color:green;">' . $success . '</div>';
}
?>

<form method="post" action="signup.php">
    <label>Name:
        <input type="text" name="name" required>
    </label><br>

    <label>Organization:
        <input type="text" name="organization">
    </label><br>

    <label>Street:
        <input type="text" name="street">
    </label><br>

    <label>City:
        <input type="text" name="city">
    </label><br>

    <label>State / Province:
        <input type="text" name="state">
    </label><br>

    <label>Country:
        <input type="text" name="country">
    </label><br>

    <label>Postal Code:
        <input type="text" name="postal_code">
    </label><br>

    <label>Introduced By (Email):
        <input type="text" name="introduced_by">
    </label><br>

    <label>Pseudonym (display name):
        <input type="text" name="pseudonym">
    </label><br>

    <label>Primary Email:
        <input type="email" name="primary_email" required>
    </label><br>

    <label>Recovery Email:
        <input type="email" name="recovery_email">
    </label><br>

    <label>Password:
        <input type="password" name="password" required>
    </label><br><br>

    <button type="submit">Create Account</button>
</form>

<?php include 'footer.php'; ?>
