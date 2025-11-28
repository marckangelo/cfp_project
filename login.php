<?php
session_start();
require 'db.php';
include 'header.php';

// Show success message from signup page
if (isset($_SESSION['signup_success']) && isset($_SESSION['new_verification_matrix'])) {
    echo '<div style="color:green;">' . $_SESSION['signup_success'] . '</div>';
    unset($_SESSION['signup_success']);

    // Extract verification matrix 16-char string
    $verification_matrix = $_SESSION['new_verification_matrix'];
    $matrix_expiry_date = $_SESSION['matrix_expiry_date'];

    // Build a 4x4 matrix (2D array) from the 16-char string (for display purposes)
        /*

        4x4 Matrix Example display:

        Verification Matrix String --> AAAABBBBCCCCDDDD

                AAAA
                BBBB
                CCCC
                DDDD
        */
        $verification_matrix_2d = array();
        for ($row = 0; $row < 4; $row++) {
            $verification_matrix_2d[$row] = array();
            for ($col = 0; $col < 4; $col++) {
                $index = $row * 4 + $col; // position in the 16-char string
                $verification_matrix_2d[$row][$col] = $verification_matrix[$index];
            }
        }

        // Display the matrix in a table with each character in a cell.
        echo "<h3>Your verification matrix (please save it now)</h3>";
        echo "<p>Expiry date: " . htmlspecialchars($matrix_expiry_date) . "</p>";

        echo "<table border='1' cellpadding='5'>";
        for ($row = 0; $row < 4; $row++) {
            echo "<tr>";
            for ($col = 0; $col < 4; $col++) {
                echo "<td>" . htmlspecialchars($verification_matrix_2d[$row][$col]) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table><br>";

        // Button to copy the verification matrix string into Clipboard
        // Alert is included when copied to clipboard by clicking the copy button.
        echo '<button type="button" onclick="copyMyText(\'' . $verification_matrix . '\')">Copy to Clipboard</button>';
}
?>

<script>
    // Function receives the text directly from the button clicked above
    function copyMyText(textToCopy) {
        
        // Standard command to write to clipboard
        navigator.clipboard.writeText(textToCopy);

        // Show the alert
        alert("Copied to clipboard successfully!");
    }
</script>

<?php
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
                $sql_admin = "SELECT admin_id 
                            FROM admin
                            WHERE admin_id = $member_id
                            LIMIT 1";

                $result_admin = mysqli_query($conn, $sql_admin);

                if ($result_admin && mysqli_num_rows($result_admin) == 1) {
                    $admin_row = mysqli_fetch_assoc($result_admin);
                    $_SESSION['is_admin'] = true;
                    $_SESSION['admin_id'] = $admin_row['admin_id'];
                } else {
                    $_SESSION['is_admin'] = false;
                    $_SESSION['admin_id'] = null;
                }

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
