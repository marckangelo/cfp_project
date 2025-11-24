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
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
    $scope = mysqli_real_escape_string($conn, $_POST['scope']);
    $formation_date = mysqli_real_escape_string($conn, $_POST['formation_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql_insert = "INSERT INTO committee (
            name,
            purpose,
            scope,
            formation_date,
            status,
            member_count
          ) VALUES (
            $name,
            $purpose,
            $scope,
            $formation_date,
            $status
        )";

    $result_insert = mysqli_query($conn, $sql_insert);

    if($result_insert) {
        $_SESSION['successful_commitee_add'] = "Commitee has successfully been added!";
        header("Location: admin_committees.php");
        exit;
    } else {
        $_SESSION['failed_commitee_add'] = "Commitee failed to be added!";
        header("Location: admin_committees.php");
        exit;
    }
    
} else {
    header("Location: login.php");
    exit;
}


?>
<h2>Statistics</h2>
<p>TODO: Form for adding a committee.</p>
<?php include 'footer.php'; ?>

<form action="admin_committees_add.php" method="post">

    <label for="name">Committee Name:</label><br>
    <input type="text" id="name" name="name" required><br><br>

    <label for="purpose">Purpose:</label><br>
    <textarea id="purpose" name="purpose" rows="4"></textarea><br><br>

    <label for="scope">Scope:</label><br>
    <select id="scope" name="scope">
        <option value="">-- Select Scope --</option>
        <option value="plagiarism">Plagiarism</option>
        <option value="content">Content</option>
        <option value="finance">Finance</option>
        <option value="appeals">Appeals</option>
    </select><br><br>

    <label for="formation_date">Formation Date:</label><br>
    <input type="date" id="formation_date" name="formation_date"><br><br>

    <label for="status">Status:</label><br>
    <select id="status" name="status">
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select><br><br>

    <label for="member_count">Member Count:</label><br>
    <input type="number" id="member_count" name="member_count" min="0"><br><br>

    <button type="submit">Create Committee</button>
</form>
