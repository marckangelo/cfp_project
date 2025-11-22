<?php
session_start();
require 'db.php';
include 'header.php';

// Temporary title of this page
echo    '<h2>Edit Profile</h2>
        <p>TODO: Implement profile edit form.</p>';

// TODO: Load existing member data and update on POST

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
    $recovery_email = trim($_POST['recovery_email']);

    $member_id = (int) $_SESSION['member_id'];

     // Build UPDATE query
    $sql_update = "
        UPDATE member
        SET
            name = '$name',
            organization = '$organization',
            pseudonym = '$pseudonym',
            street = '$street',
            city = '$city',
            postal_code = '$postal_code',
            primary_email = '$primary_email',
            recovery_email = '$recovery_email'
        WHERE member_id = $member_id
    ";

    // Run update query
    $result_update = mysqli_query($conn, $sql_update);

    if ($result_update) {
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
    echo '<a href="my_account.php"><button type="button">Cancel</button></a>';
    echo '</form>';

} else {
    header("Location: login.php");
    exit;
}

?>

<?php include 'footer.php'; ?>
