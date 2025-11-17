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
    $name = trim($_POST['name']);
    $organization = trim($_POST['organization']);
    $primary_email = trim($_POST['primary_email']);
    $recovery_email = trim($_POST['recovery_email']);
    $password = trim($_POST['password']);

    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $country = trim($_POST['country']);
    $postal_code = trim($_POST['postal_code']);

    $introduced_by = trim($_POST['introduced_by']);
    $pseudonym = trim($_POST['pseudonym']);
    $orcid = trim($_POST['orcid']);

    // 2. Basic validation (very simple)

    // Name required
    if ($name == "") {
        $errors[] = "Name is required.";
    }

    // Organization required (spec says so)
    if ($organization == "") {
        $errors[] = "Organization is required.";
    }

    // Basic address required (you can refine later if needed)
    if ($street == "" || $city == "" || $country == "" || $postal_code == "") {
        $errors[] = "Address is required (street, city, country and postal code).";
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

    // Recovery email required
    // super basic email check: must contain format like alphanumeric@domain.com
    if ($recovery_email == "") {
    $errors[] = "Recovery email is required.";
    } else {
        // Same simple regex as primary email check
        if (!preg_match("/^[a-zA-Z0-9]+@[a-zA-Z0-9\.]+\.(com|ca|org)$/", $recovery_email)) {
            $errors[] = "Recovery email is not valid. Use format like: alphanumeric@domain.com (example: user@cs.concordia.ca).";
        }
        elseif ($recovery_email == $primary_email) {
            $errors[] = "Recovery Email cannot be the same as the Primary email.";
        }
    }

    // Introduced By email required by spec (must be an existing member)
    if ($introduced_by == "") {
        $errors[] = "Introduced By email is required.";
    } else {
        // Use same simple regex
        if (!preg_match("/^[a-zA-Z0-9]+@[a-zA-Z0-9\.]+\.(com|ca|org)$/", $introduced_by)) {
            $errors[] = "Introduced By email is not valid. Use format like: alphanumeric@domain.com.";
        }
    }

    // Password length check (basic)
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    // 3. If no validation errors so far, check if primary email already exists
    if (count($errors) == 0) {

      // Check if primary email already exists
        $sql_check = "SELECT * FROM member WHERE primary_email = '$primary_email'";
        $result_check = mysqli_query($conn, $sql_check);

        if ($result_check) {
            if (mysqli_num_rows($result_check) > 0) {
                $errors[] = "This email is already registered.";
            }
        } else {
            $errors[] = "Database error while checking email.";
        }

        // If the user typed an introduced_by email, check if it exists in the member table
        $sql_intro = "SELECT * FROM member WHERE primary_email = '$introduced_by'";
        $result_intro = mysqli_query($conn, $sql_intro);
            
        if ($result_intro) {
            // If no rows returned, introducer does NOT exist
            if (mysqli_num_rows($result_intro) == 0) {                    
                $errors[] = "Introduced By email does not exist in the member list.";
            }
        } else {                
            $errors[] = "Database error while checking Introduced By email.";
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

            // Get the member_id that was most recently inserted (built-in MySql function)
            $member_id = mysqli_insert_id($conn);

            // If ORCID was provided, then also insert into AUTHOR table
            if($orcid != "") {
                $sql_author = "INSERT INTO author (member_id, orcid)
                               VALUES ($member_id, $orcid)";
                mysqli_query($conn, $sql_author);
            }

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
        <input type="text" name="organization" required>
    </label><br>

    <label>Street:
        <input type="text" name="street" required>
    </label><br>

    <label>City:
        <input type="text" name="city" required>
    </label><br>

    <label>State / Province:
        <input type="text" name="state">
    </label><br>

    <label>Country:
        <input type="text" name="country" required>
    </label><br>

    <label>Postal Code:
        <input type="text" name="postal_code" required>
    </label><br>

    <label>Introduced By (Email):
        <input type="email" name="introduced_by" required>
    </label><br>

    <label>Pseudonym (display name):
        <input type="text" name="pseudonym">
    </label><br>

     <label>ORCID (optional, for Authors):
        <input type="text" name="orcid">
    </label><br>

    <label>Primary Email:
        <input type="email" name="primary_email" required>
    </label><br>

    <label>Recovery Email:
        <input type="email" name="recovery_email" required>
    </label><br>

    <label>Password:
        <input type="password" name="password" required>
    </label><br><br>

    <button type="submit">Create Account</button>

</form>

<?php include 'footer.php'; ?>
