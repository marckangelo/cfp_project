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
include 'header.php';

// Make sure required session data exists
if (!isset($_SESSION['matrix_member_id']) ||
    !isset($_SESSION['new_verification_matrix']) ||
    !isset($_SESSION['matrix_expiry_date'])) {

    echo "<p style='color:red;'>No verification matrix found in session.</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    include 'footer.php';
    exit;
}

$member_id           = (int) $_SESSION['matrix_member_id'];
$verification_matrix = $_SESSION['new_verification_matrix'];
$matrix_expiry_date  = $_SESSION['matrix_expiry_date'];

// Get today's date (for expiry logic)
$today_date = date("Y-m-d");

// If verfication_matrix is expired
if ($today_date > $matrix_expiry_date || isset($_SESSION['verification_matrix_expired'])) {
    // Unset, won't be needed anymore
    unset($_SESSION['verification_matrix_expired']);

    // Verification matrix: random 16-character string
    $chars                       = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $renewed_verification_matrix = "";

    // loop where it concatenates 1 random character 16 times to create the verification_matrix
    for ($i = 0; $i < 16; $i++) {
        $renewed_verification_matrix .= $chars[rand(0, strlen($chars) - 1)];
    }

    // New expiry date: 30 days from now
    $renewed_matrix_expiry_date = date("Y-m-d", strtotime("+30 days"));

    $safe_renewed_matrix = mysqli_real_escape_string($conn, $renewed_verification_matrix);

    $sql_update_matrix = "UPDATE member
                          SET matrix_expiry_date = '$renewed_matrix_expiry_date',
                              verification_matrix = '$safe_renewed_matrix'
                          WHERE member_id = $member_id";

    $result_update = mysqli_query($conn, $sql_update_matrix);

    if ($result_update) {
        // Update session + local variables
        $_SESSION['new_verification_matrix'] = $renewed_verification_matrix;
        $_SESSION['matrix_expiry_date']      = $renewed_matrix_expiry_date;

        $verification_matrix = $renewed_verification_matrix;
        $matrix_expiry_date  = $renewed_matrix_expiry_date;

        echo "<div style='color:green;'>Your previous verification matrix was expired. A new one has been generated.</div>";
    } else {
        echo "<p style='color:red;'>Failed to update verification matrix in the database.</p>";
    }
}

// Show success message from signup (if set)
if (isset($_SESSION['signup_success'])) {
    echo '<div style="color:green;">' . $_SESSION['signup_success'] . '</div>';
    // Unset it so it only shows once:
    unset($_SESSION['signup_success']);
}

// Build a 4x4 matrix (2D array) from the 16-char string
/*
    Example: 16-char verification_matrix string = 'AAAABBBBCCCCDDDD'

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

// wrapper + classed table
echo '<div class="matrix-wrapper">';
echo '<h3 class="centered-title">Your verification matrix (please save it now)</h3>';
echo '<p>Expiry date: ' . htmlspecialchars($matrix_expiry_date) . '</p>';

echo "<table class='matrix-table'>";
for ($row = 0; $row < 4; $row++) {
    echo "<tr>";
    for ($col = 0; $col < 4; $col++) {
        echo "<td>" . htmlspecialchars($verification_matrix_2d[$row][$col]) . "</td>";
    }
    echo "</tr>";
}
echo "</table><br>";

// Button to copy the verification matrix string into Clipboard
echo '<button type="button" onclick="copyMyText(\'' . $verification_matrix . '\')">Copy to Clipboard</button><br><br>';

echo '<form method="get" action="login.php">
        <button type="submit">Go to Login</button>
      </form>';

echo '</div>'; // end .matrix-wrapper
?>

<script>
    function copyMyText(textToCopy) {
        navigator.clipboard.writeText(textToCopy);
        alert("Copied to clipboard successfully!");
    }
</script>
<br><br>
<a href="login.php">
    Proceed to login page
</a>

<?php include 'footer.php'; ?>