<?php
/*
Contributor to this file:
- Muhammad RAZA (40284058)
*/

session_start();
require 'db.php';
// include 'header.php';

$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Get and clean the input
    $verification_input = strtoupper(trim($_POST['verification_matrix']));

    // 2. Basic validation
    if ($verification_input == "") {
        $errors[] = "Verification matrix is required.";
    } else {
        // Must be exactly 16 chars, A-Z and 0–9
        if (!preg_match("/^[A-Z0-9]{16}$/", $verification_input)) {
            $errors[] = "Verification matrix must be exactly 16 characters (A–Z, 0–9).";
        }
    }

    // 3. If no validation errors so far, check against DB
    if (count($errors) == 0) {

        // Must have a logged-in member to verify against
        if (!isset($_SESSION['member_id'])) {
            $errors[] = "You must be logged in before verifying your matrix.";
        } else {

            $member_id = (int)$_SESSION['member_id'];

            $sql = "SELECT verification_matrix, matrix_expiry_date
                    FROM member
                    WHERE member_id = $member_id
                    LIMIT 1";

            $result = mysqli_query($conn, $sql);

            if ($result && mysqli_num_rows($result) == 1) {

                $row = mysqli_fetch_assoc($result);

                $stored_matrix = strtoupper($row['verification_matrix']);
                $expiry_date   = $row['matrix_expiry_date'];
                $today         = date("Y-m-d");

                // Check matrix matches
                if ($stored_matrix !== $verification_input) {
                    $errors[] = "Verification matrix does not match our records.";
                }
                // Check expiry
                elseif ($expiry_date !== null && $expiry_date < $today) {

                    $_SESSION['verification_matrix_expired'] = "Verification matrix has expired.";
                    $_SESSION['new_verification_matrix'] = $stored_matrix;
                    $_SESSION['matrix_member_id'] = $member_id;
                    $_SESSION['matrix_expiry_date'] = $expiry_date;
                    header("Location: matrix_display.php");
                    exit;
                } else {
                    // SUCCESS: matrix is correct (and not expired)
                    $_SESSION['matrix_verified'] = true;
                    header("Location: index.php");
                    exit;
                }

            } else {
                $errors[] = "Verification data not found for this account.";
            }
        }
    }
}
include 'header.php';
?>

<?php
// Show errors if there are any (using .errors from style.css)
if (count($errors) > 0) {
    echo '<div class="errors"><ul>';
    for ($i = 0; $i < count($errors); $i++) {
        echo '<li>' . htmlspecialchars($errors[$i]) . '</li>';
    }
    echo '</ul></div>';
}
?>

<div class="centered-form">
    <h2 class="centered-title">Matrix Verification</h2>
    <form method="post" action="matrix_verification.php">
        <label>
            Verification Matrix String:
            <input type="text" name="verification_matrix" required maxlength="16" placeholder="Paste your 16-char code here">
        </label>
        <br><br>
        <button type="submit">Log In</button>
    </form>
</div>

<?php include 'footer.php'; ?>