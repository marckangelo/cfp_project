<?php
session_start();
require 'db.php';
include 'header.php';

// Temporary title of this page
echo    '<h2>Edit Profile</h2>';

// Process the form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Extract form values
    $name = trim($_POST['name']);
    $organization = trim($_POST['organization']);
    $pseudonym = trim($_POST['pseudonym']);
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $primary_email = trim($_POST['primary_email']);
    $recovery_email= trim($_POST['recovery_email']);
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';

    $member_id = (int) $_SESSION['member_id'];

    // Escape strings for safety
    $safe_name = mysqli_real_escape_string($conn, $name);
    $safe_org = mysqli_real_escape_string($conn, $organization);
    $safe_pseudonym = mysqli_real_escape_string($conn, $pseudonym);
    $safe_street = mysqli_real_escape_string($conn, $street);
    $safe_city = mysqli_real_escape_string($conn, $city);
    $safe_postal = mysqli_real_escape_string($conn, $postal_code);
    $safe_primary_email = mysqli_real_escape_string($conn, $primary_email);
    $safe_recovery_email= mysqli_real_escape_string($conn, $recovery_email);
    $safe_bio = mysqli_real_escape_string($conn, $bio);

    // Build UPDATE query for member
    $sql_update = "
        UPDATE member
        SET
            name = '$safe_name',
            organization = '$safe_org',
            pseudonym = '$safe_pseudonym',
            street = '$safe_street',
            city = '$safe_city',
            postal_code = '$safe_postal',
            primary_email = '$safe_primary_email',
            recovery_email = '$safe_recovery_email'
        WHERE member_id = $member_id
    ";

    // Run update query
    $result_update = mysqli_query($conn, $sql_update);

    if ($result_update) {
        // Also update BIO in author table if this member is an author
        // If the member is not an author, this UPDATE won't be executed
        $sql_update_bio = "
            UPDATE author
            SET bio = '$safe_bio'
            WHERE member_id = $member_id
        ";
        mysqli_query($conn, $sql_update_bio);

        // Save success message in session
        $_SESSION['profile_success'] = "Profile successfully updated!";
        // Redirect back to my_account.php
        header("Location: my_account.php");
        exit;
    } else {
        echo "<div style='color:red;'>Error updating profile. Try again.</div>";
    }
}

// Checking if signed in
if (isset($_SESSION['member_id'])) {

    // Load member details + author bio (if any)
    $sql_member_details = "SELECT m.*, a.bio, a.member_id AS is_author
                           FROM member m
                           LEFT JOIN author a ON m.member_id = a.member_id
                           WHERE m.member_id = " . (int)$_SESSION['member_id'];

    // Run the query
    $result_member_details = mysqli_query($conn, $sql_member_details);

    // Fetch the data
    $row = mysqli_fetch_assoc($result_member_details);

    $is_author = !empty($row['is_author']); // true if there's an author row

    // Form
    echo '
    <div class="profile-form">
    <form method="post" action="edit_profile.php">

    <h4>Member Details</h4>

        <table border="1">
            <tr>
                <th>Name</th>
                <th>organization</th>
                <th>Pseudonym</th>
                <th>Address</th>
                <th>Primary Email</th>
                <th>Recovery Email</th>';

    // Only show Bio column if this member is an author
    if ($is_author) {
        echo '
                <th>Bio</th>';
    }

    echo '
            </tr>
    ';
    
    // Table row
    echo '
            <tr>
                <td>
                    <input type="text" name="name" value="' . htmlspecialchars($row['name']) . '" required>
                </td>
                <td>
                    <input type="text" name="organization" value="' . htmlspecialchars($row['organization']) . '" required>
                </td>
                <td>
                    <input type="text" name="pseudonym" value="' . htmlspecialchars($row['pseudonym']) . '" required>
                </td>
                <td>
                    <label>Street:
                        <input type="text" name="street" value="' . htmlspecialchars($row['street']) . '" required>
                    </label><br>
                    <label>City:
                        <input type="text" name="city" value="' . htmlspecialchars($row['city']) . '" required>
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
                </td>';
    
    // Bio cell only if author
    if ($is_author) {
        echo '
                <td>
                    <textarea name="bio" rows="4" cols="30">' . htmlspecialchars($row['bio']) . '</textarea>
                </td>';
    }

    echo '
            </tr>
        ';
    echo '</table><br>';

    echo '<button type="submit">Save Changes</button> ';
    echo '<a href="my_account.php"><button type="button">Cancel</button></a>';
    echo '</form>';
    
    echo '</div>'; // close div tag

} else {
    header("Location: login.php");
    exit;
}

?>

<?php include 'footer.php'; ?>