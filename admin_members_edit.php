<?php
session_start();
require 'db.php';
/*
- Marck Angelo GELI (40265711)
- Arshdeep SINGH (40286514)
- Muhammad Adnan SHAHZAD (40282531)
- Muhammad RAZA (40284058)
*/

/*
Contributor to this file:
- Marck Angelo GELI (40265711)
*/

// Restrict to admins only (adjust to your session keys if needed)
if (empty($_SESSION['is_admin']) || empty($_SESSION['admin_role'])) {
    echo "<p>You must be an admin to access this page.</p>";
    include 'footer.php';
    exit;
}

// Title of this page
echo    '<h2 class="centered-title">Edit Member (Admin)</h2>
        <p>TODO: Implement admin member edit form.</p>';

// ===================== PROCESS FORM (POST) =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Extract form values
    $member_id = (int) $_POST['member_id'];
    $name = trim($_POST['name']);
    $organization = trim($_POST['organization']);
    $pseudonym = trim($_POST['pseudonym']);
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $country = trim($_POST['country']);
    $postal_code = trim($_POST['postal_code']);
    $primary_email = trim($_POST['primary_email']);
    $recovery_email = trim($_POST['recovery_email']);
    $status = trim($_POST['status']);

    // Escape strings for safety
    $safe_name = mysqli_real_escape_string($conn, $name);
    $safe_org = mysqli_real_escape_string($conn, $organization);
    $safe_pseudonym = mysqli_real_escape_string($conn, $pseudonym);
    $safe_street = mysqli_real_escape_string($conn, $street);
    $safe_city = mysqli_real_escape_string($conn, $city);
    $safe_country = mysqli_real_escape_string($conn, $country);
    $safe_postal = mysqli_real_escape_string($conn, $postal_code);
    $safe_primary_email = mysqli_real_escape_string($conn, $primary_email);
    $safe_recovery_email= mysqli_real_escape_string($conn, $recovery_email);
    $safe_status = mysqli_real_escape_string($conn, $status);

    // Build UPDATE query for this member
    $sql_update = "
        UPDATE member
        SET
            name = '$safe_name',
            organization = '$safe_org',
            pseudonym = '$safe_pseudonym',
            street = '$safe_street',
            city = '$safe_city',
            country = '$safe_country',
            postal_code = '$safe_postal',
            primary_email = '$safe_primary_email',
            recovery_email = '$safe_recovery_email',
            status = '$safe_status'
        WHERE member_id = $member_id
    ";

    // Run update query
    $result_update = mysqli_query($conn, $sql_update);

    if ($result_update) {
        // Save success message in session
        $_SESSION['admin_member_update_success'] = "Member profile successfully updated!";
        // Redirect back to admin_members.php
        header("Location: admin_members.php");
        exit;
    } else {
        echo "<div style='color:red;'>Error updating member profile. Try again.</div>";
    }
}

// ===================== LOAD MEMBER TO EDIT (GET) =====================

// We need a member_id to edit: get info through GET, using POST if needed
if (isset($_GET['member_id'])) {
    $member_id = (int) $_GET['member_id'];
} elseif (isset($_POST['member_id'])) {
    $member_id = (int) $_POST['member_id'];
} else {
    echo "<div style='color:red;'>No member selected for editing.</div>";
    include 'footer.php';
    exit;
}

// Load member data from DB
$sql_member_details = "SELECT *
                       FROM member
                       WHERE member_id = $member_id";

$result_member_details = mysqli_query($conn, $sql_member_details);

if (!$result_member_details || mysqli_num_rows($result_member_details) == 0) {
    echo "<div style='color:red;'>Member not found.</div>";
    include 'footer.php';
    exit;
}

// Fetch the data
$row = mysqli_fetch_assoc($result_member_details);
include 'header.php';
// ===================== SHOW EDIT FORM =====================

echo '

<form method="post" action="admin_members_edit.php">

    <input type="hidden" name="member_id" value="' . (int)$row['member_id'] . '">

    <h4>Edit Member Details</h4>

    <table border="1">
        <tr>
            <th>Name</th>
            <th>organization</th>
            <th>Pseudonym</th>
            <th>Address</th>
            <th>Primary Email</th>
            <th>Recovery Email</th>
            <th>Status</th>
        </tr>
        <tr>
            <td>
                <input type="text" name="name" value="' . htmlspecialchars($row['name']) . '" required>
            </td>
            <td>
                <input type="text" name="organization" value="' . htmlspecialchars($row['organization']) . '" required>
            </td>
            <td>
                <input type="text" name="pseudonym" value="' . htmlspecialchars($row['pseudonym']) . '">
            </td>
            <td>
                <label>Street:
                    <input type="text" name="street" value="' . htmlspecialchars($row['street']) . '" required>
                </label><br>
                <label>City:
                    <input type="text" name="city" value="' . htmlspecialchars($row['city']) . '" required>
                </label><br>
                <label>Country:
                    <input type="text" name="country" value="' . htmlspecialchars($row['country']) . '" required>
                </label><br>
                <label>Postal Code:
                    <input type="text" name="postal_code" value="' . htmlspecialchars($row['postal_code']) . '" required>
                </label><br>
            </td>
            <td>
                <input type="text" name="primary_email" value="' . htmlspecialchars($row['primary_email']) . '" required>
            </td>
            <td>
                <input type="text" name="recovery_email" value="' . htmlspecialchars($row['recovery_email']) . '" required>
            </td>
            <td>
                <input type="text" name="status" value="' . htmlspecialchars($row['status']) . '" required>
            </td>
        </tr>
    </table><br>

    <button type="submit">Save Changes</button>
    <a href="admin_members.php"><button type="button">Cancel</button></a>

</form>
';

include 'footer.php';
?>