<?php
session_start();
require 'db.php';
include 'header.php';

echo    '<h2>Edit Profile</h2>
        <p>TODO: Implement profile edit form.</p>';

// TODO: Load existing member data and update on POST

// Checking if signed in
if (isset($_SESSION['member_id'])) {
    // DISPLAY MEMBER DETAILS
    $sql_member_details = "SELECT * 
                           FROM member
                           WHERE member_id = " . $_SESSION['member_id'];
    
    // Run the query
    $result_member_details = mysqli_query($conn, $sql_member_details);

    // Fetch the data
    $row = mysqli_fetch_assoc($result_member_details);

    // Table header
    echo '

    <form method="post" action="edit_profile.php">

    <h4>Edit Member Details</h4>

        <table border="1">
            <tr>
                <th>Name</th>
                <th>organization</th>
                <th>Pseudonym</th>
                <th>Address</th>
                <th>Primary Email</th>
                <th>Recovery Email</th>
            </tr>
    ';
    
    //Table rows
        echo '
            <tr>
                <td>
                    <input type="text" name="name" value="' . $row['name'] . '" required>
                </td>
                <td>
                    <input type="text" name="organization" value="' . $row['organization'] . '" required>
                </td>
                <td>
                    <label>Pseudonym:
                        <input type="text" name="pseudonym" value="' . $row['pseudonym'] . '" required>
                    </label><br>
                </td>
                
                <td>
                    <label>Street:
                        <input type="text" name="street" value="' . $row['street'] . '" required>
                    </label><br>
                    <label>City:
                        <input type="text" name="city" value="' . $row['city'] . '" required>
                    </label><br>
                    <label>Postal Code:
                        <input type="text" name="postal_code" value="' . $row['postal_code'] . '" required>
                    </label><br>
                </td>
                <td>
                    <input type="text" name="primary_email" value="' . $row['primary_email'] . '" required>
                </td>
                <td>
                    <input type="text" name="recovery_email" value="' . $row['recovery_email'] . '" required>
                </td>
            </tr>
        ';
    echo '</table><br>';

    echo '<button type="submit">Save Changes</button>';

} else {
    header("Location: login.php");
}

?>

<!-- <form method="post" action="signup.php">

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

</form> -->

<?php include 'footer.php'; ?>
