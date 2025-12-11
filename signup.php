<?php
/*
- Marck Angelo GELI (40265711)
- Arshdeep SINGH (40286514)
- Muhammad Adnan SHAHZAD (40282531)
- Muhammad RAZA (40284058)
*/

/*
Contributor to this file:
- Muhammad RAZA (40284058)
*/

session_start();
require 'db.php';

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
    $bio = trim($_POST['bio']);

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
        // Super basic email check: must contain format like alphanumeric@domain.com
        if (!preg_match("/^[a-zA-Z0-9]+@[a-zA-Z0-9\.]+\.(com|ca|org)$/", $primary_email)) {
            $errors[] = "Primary email is not valid. Use format like: alphanumeric@domain.com (example: user@cs.concordia.ca).";
        }
    }

    // Recovery email required
    if ($recovery_email == "") {
        $errors[] = "Recovery email is required.";
    } else {
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
        $sql_intro = "SELECT member_id FROM member WHERE primary_email = '$introduced_by'";
        $result_intro = mysqli_query($conn, $sql_intro);
            
        if ($result_intro) {
            // If no rows returned, introducer does NOT exist
            if (mysqli_num_rows($result_intro) == 0) {                    
                $errors[] = "Introduced By email does not exist in the member list.";
            } else {
                // fetch the introducer's member_id
                $row_intro = mysqli_fetch_assoc($result_intro);
                $introduced_by_id = (int)$row_intro['member_id'];
            }
        } else {                
            $errors[] = "Database error while checking Introduced By email.";
        }
    }

    // 4. If still no errors, insert into database
    if (count($errors) == 0) {

        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Simple values
        $join_date = date("Y-m-d");
        $status = "active";

        // Verification matrix: random 16-character string
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $verification_matrix = "";

        for ($i = 0; $i < 16; $i++) {
            $verification_matrix .= $chars[rand(0, strlen($chars) - 1)];
        }

        // Expiry date: 30 days from now
        $matrix_expiry_date = date("Y-m-d", strtotime("+30 days"));

        $sql = "INSERT INTO member
                (name, organization, primary_email, recovery_email, password_hash, join_date, status,
                street, city, state, country, postal_code,
                introduced_by, pseudonym,
                verification_matrix, matrix_expiry_date)
                VALUES
                ('$name', '$organization', '$primary_email', '$recovery_email', '$password_hash', '$join_date', '$status',
                '$street', '$city', '$state', '$country', '$postal_code',
                '$introduced_by_id', '$pseudonym',
                '$verification_matrix', '$matrix_expiry_date')";

        if (mysqli_query($conn, $sql)) {

            // Get the member_id that was most recently inserted
            $member_id = mysqli_insert_id($conn);

            // If ORCID was provided, then also insert into AUTHOR table
            if ($orcid != "") {
                // ORCID format check --> Must be like: 0000-0000-0000-0000
                if (!preg_match("/^[0-9]{4}-[0-9]{4}-[0-9]{4}-[0-9]{4}$/", $orcid)) {
                    $errors[] = "ORCID is not valid. Use format like: 0000-0000-0000-0000.";
                } else {
                    // Escape bio so quotes don't break the query
                    $bio_safe = mysqli_real_escape_string($conn, $bio);

                    // Insert bio along with author record
                    $sql_author = "INSERT INTO author (member_id, orcid, bio)
                                   VALUES ($member_id, '$orcid', '$bio_safe')";
                    mysqli_query($conn, $sql_author);
                }
            }

            // Save matrix and its expiry date in session so we can show it once on login page
            $_SESSION['new_verification_matrix'] = $verification_matrix;
            $_SESSION['matrix_expiry_date'] = $matrix_expiry_date;
            $_SESSION['matrix_member_id'] = $member_id;
            
            // If this is reached, signup was successful -> head to matrix display
            $_SESSION['signup_success'] = "Account created successfully!";
            header("Location: matrix_display.php");
            exit;
        }
    }
}
include 'header.php';
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

    <label>Bio (optional, for Authors):
        <textarea name="bio" rows="4" cols="40"></textarea>
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
