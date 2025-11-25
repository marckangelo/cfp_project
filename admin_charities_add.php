<?php
session_start();
require 'db.php';
include 'header.php';

// // Checks if user is an admin
// if (isset($_SESSION['admin_id'])) {
//     // PUT EVERYTHING IN HERE
// } else {
//     header("Location: login.php");
//     exit;
// }

// Process form 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Extract values from form submitted
    $name               = mysqli_real_escape_string($conn, $_POST['name']);
    $description        = mysqli_real_escape_string($conn, $_POST['description']);
    $mission            = mysqli_real_escape_string($conn, $_POST['mission']);
    $country            = mysqli_real_escape_string($conn, $_POST['country']);
    $registration_number= mysqli_real_escape_string($conn, $_POST['registration_number']);
    $status             = mysqli_real_escape_string($conn, $_POST['status']);

    // total_received starts at 0
    $sql_insert = "
        INSERT INTO charity (
            name,
            description,
            mission,
            country,
            registration_number,
            status,
            total_received
        ) VALUES (
            '$name',
            '$description',
            '$mission',
            '$country',
            '$registration_number',
            '$status',
            0.00
        )
    ";

    $result_insert = mysqli_query($conn, $sql_insert);

    if ($result_insert) {
        $_SESSION['successful_charity_add'] = "Charity has successfully been added!";
        header("Location: admin_charities.php");
        exit;
    } else {
        $_SESSION['failed_charity_add'] = "Charity failed to be added!";
        header("Location: admin_charities.php");
        exit;
    }
}
?>

<h2>Admin - Add Charity</h2>
<p>TODO: Form for adding a charity.</p>
<?php include 'footer.php'; ?>

<form action="admin_charities_add.php" method="post">

    <label for="name">Charity Name:</label><br>
    <input type="text" id="name" name="name" required><br><br>

    <label for="description">Description:</label><br>
    <textarea id="description" name="description" rows="4"></textarea><br><br>

    <label for="mission">Mission:</label><br>
    <textarea id="mission" name="mission" rows="3"></textarea><br><br>

    <label for="country">Country:</label><br>
    <input type="text" id="country" name="country"><br><br>

    <label for="registration_number">Registration Number:</label><br>
    <input type="text" id="registration_number" name="registration_number" required><br><br>

    <label for="status">Status:</label><br>
    <select id="status" name="status">
        <option value="pending">Pending</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select><br><br>

    <button type="submit">Create Charity</button>
</form>
