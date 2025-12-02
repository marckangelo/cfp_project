<?php
session_start();
require 'db.php';
// include 'header.php';

// // Checks if user is an admin
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login.php");
//     exit;
// }

// ================== PROCESS FORM IF FORM WAS SUBMITTED ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $charity_id = (int) $_POST['charity_id'];

    // Extract values from form submitted
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $mission = mysqli_real_escape_string($conn, $_POST['mission']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $registration_number = mysqli_real_escape_string($conn, $_POST['registration_number']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Build the MySQL UPDATE query
    $sql_update = "
        UPDATE charity
        SET
            name = '$name',
            description = '$description',
            mission = '$mission',
            country = '$country',
            registration_number = '$registration_number',
            status = '$status'
        WHERE charity_id = $charity_id
    ";

    // Run the UPDATE query
    $result_update = mysqli_query($conn, $sql_update);

    if ($result_update) {
        $_SESSION['successful_charity_edit'] = "Charity has been updated successfully!";
    } else {
        $_SESSION['failed_charity_edit'] = "Failed to update charity.";
    }

    header("Location: admin_charities.php");
    exit;
}

// ================== LOAD EXISTING ROW (SHOW FORM) ==================

// We expect charity_id via GET when clicking "Edit"
if (!isset($_GET['charity_id'])) {
    // No id = nothing to edit -> go back
    header("Location: admin_charities.php");
    exit;
}

$charity_id = (int) $_GET['charity_id'];

// Get the existing row's details
$sql_select = "SELECT * FROM charity WHERE charity_id = $charity_id";

// Run the SELECT query
$result_select = mysqli_query($conn, $sql_select);

include 'header.php';

if (!$result_select || mysqli_num_rows($result_select) === 0) {
    echo "<p>Charity not found.</p>";
    include 'footer.php';
    exit;
}

$charity = mysqli_fetch_assoc($result_select);
?>

<h2>Edit Charity</h2>

<form action="admin_charities_edit.php" method="post">

    <!-- keep id in a hidden field so POST knows what to update -->
    <input type="hidden" name="charity_id" value="<?php echo (int)$charity['charity_id']; ?>">

    <label for="name">Charity Name:</label><br>
    <input type="text" id="name" name="name"
           value="<?php echo htmlspecialchars($charity['name']); ?>" required><br><br>

    <label for="description">Description:</label><br>
    <textarea id="description" name="description" rows="4"><?php
        echo htmlspecialchars($charity['description']);
    ?></textarea><br><br>

    <label for="mission">Mission:</label><br>
    <textarea id="mission" name="mission" rows="3"><?php
        echo htmlspecialchars($charity['mission']);
    ?></textarea><br><br>

    <label for="country">Country:</label><br>
    <input type="text" id="country" name="country"
           value="<?php echo htmlspecialchars($charity['country']); ?>"><br><br>

    <label for="registration_number">Registration Number:</label><br>
    <input type="text" id="registration_number" name="registration_number"
           value="<?php echo htmlspecialchars($charity['registration_number']); ?>" required><br><br>

    <label for="status">Status:</label><br>
    <select id="status" name="status">
        <option value="pending" <?php if ($charity['status'] === 'pending') echo 'selected'; ?>>Pending</option>
        <option value="active"  <?php if ($charity['status'] === 'active')  echo 'selected'; ?>>Active</option>
        <option value="inactive"<?php if ($charity['status'] === 'inactive')echo 'selected'; ?>>Inactive</option>
    </select><br><br>

    <p><strong>Total Received:</strong>
        <?php echo htmlspecialchars($charity['total_received']); ?>
    </p>

    <button type="submit">Save Changes</button>
</form>

<?php include 'footer.php'; ?>