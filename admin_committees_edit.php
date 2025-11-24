<?php
session_start();
require 'db.php';
include 'header.php';

// // Checks if user is an admin
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: login.php");
//     exit;
// }

// ================== PROCESS FORM IF FORM WAS SUBMITTED ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $committee_id   = (int) $_POST['committee_id'];

    // Extact values from form submitted
    $name           = mysqli_real_escape_string($conn, $_POST['name']);
    $purpose        = mysqli_real_escape_string($conn, $_POST['purpose']);
    $scope          = mysqli_real_escape_string($conn, $_POST['scope']);
    $formation_date = mysqli_real_escape_string($conn, $_POST['formation_date']);
    $status         = mysqli_real_escape_string($conn, $_POST['status']);
    $member_count   = (int) $_POST['member_count'];

    // Build the MySql UPDATE query
    $sql_update = "
        UPDATE committee
        SET
            name = '$name',
            purpose = '$purpose',
            scope = '$scope',
            formation_date = '$formation_date',
            status = '$status',
            member_count = $member_count
        WHERE committee_id = $committee_id
    ";

    // Run the UPDATE query
    $result_update = mysqli_query($conn, $sql_update);


    if ($result_update) {
        $_SESSION['successful_committee_edit'] = "Committee has been updated successfully!";
    } else {
        $_SESSION['failed_committee_edit'] = "Failed to update committee.";
    }

    header("Location: admin_committees.php");
    exit;
}

// ================== LOAD EXISTING ROW (SHOW FORM) ==================

// We expect committee_id via GET when clicking "Edit"
if (!isset($_GET['committee_id'])) {
    // No id = nothing to edit -> go back
    header("Location: admin_committees.php");
    exit;
}

$committee_id = (int) $_GET['committee_id'];

// Get the existing row's details
$sql_select = "SELECT * FROM committee WHERE committee_id = $committee_id";

// Run the SELECT query
$result_select = mysqli_query($conn, $sql_select);

if (!$result_select || mysqli_num_rows($result_select) === 0) {
    echo "<p>Committee not found.</p>";
    include 'footer.php';
    exit;
}

$committee = mysqli_fetch_assoc($result_select);
?>

<h2>Edit Committee</h2>

<form action="admin_committees_edit.php" method="post">

    <!-- keep id in a hidden field so POST knows what to update -->
    <input type="hidden" name="committee_id" value="<?php echo (int)$committee['committee_id']; ?>">

    <label for="name">Committee Name:</label><br>
    <input type="text" id="name" name="name"
           value="<?php echo htmlspecialchars($committee['name']); ?>" required><br><br>

    <label for="purpose">Purpose:</label><br>
    <textarea id="purpose" name="purpose" rows="4"><?php
        echo htmlspecialchars($committee['purpose']);
    ?></textarea><br><br>

    <label for="scope">Scope:</label><br>
    <select id="scope" name="scope">
        <option value="">-- Select Scope --</option>
        <option value="plagiarism" <?php if ($committee['scope'] === 'plagiarism') echo 'selected'; ?>>Plagiarism</option>
        <option value="content"    <?php if ($committee['scope'] === 'content')    echo 'selected'; ?>>Content</option>
        <option value="finance"    <?php if ($committee['scope'] === 'finance')    echo 'selected'; ?>>Finance</option>
        <option value="appeals"    <?php if ($committee['scope'] === 'appeals')    echo 'selected'; ?>>Appeals</option>
    </select><br><br>

    <label for="formation_date">Formation Date:</label><br>
    <input type="date" id="formation_date" name="formation_date"
           value="<?php echo htmlspecialchars($committee['formation_date']); ?>"><br><br>

    <label for="status">Status:</label><br>
    <select id="status" name="status">
        <option value="active"   <?php if ($committee['status'] === 'active')   echo 'selected'; ?>>Active</option>
        <option value="inactive" <?php if ($committee['status'] === 'inactive') echo 'selected'; ?>>Inactive</option>
    </select><br><br>

    <label for="member_count">Member Count:</label><br>
    <input type="number" id="member_count" name="member_count" min="0"
           value="<?php echo (int)$committee['member_count']; ?>"><br><br>

    <button type="submit">Save Changes</button>
</form>

<?php include 'footer.php'; ?>
