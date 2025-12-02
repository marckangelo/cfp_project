<?php
session_start();
require 'db.php';
include 'header.php';

// Checking if signed in as member **** SHOULD BE CHECKING IF SIGNED IN AS ADMIN as well****
if (isset($_SESSION['member_id'])) {
    // DISPLAY MEMBER DETAILS
    $sql_author_details = "SELECT 
                          a.orcid,
                          m.name,
                          m.organization,
                          m.primary_email,
                          a.specialization,
                          a.h_index,
                          a.total_downloads
                          FROM author a
                          JOIN member m ON a.member_id = m.member_id
                          ORDER BY m.name ASC
                          ";

    // Run the query
    $result_author_details = mysqli_query($conn, $sql_author_details);
}
else {
    // Redirect to login page if not signed in
    echo "<p> You must be signed in to view author details. Redirecting to login page...</p>";
    header("Location: login.php");
    exit();
}
?>

<h2 class="centered-title">Authors</h2>
<table border="1">
    <tr>
        <th>Orcid</th>
        <th>Name</th>
        <th>Organization</th>
        <th>Primary Email</th>
        <th>Specialization</th>
        <th>h index</th>
        <th>Total Downloads</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result_author_details)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['orcid']); ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['organization']); ?></td>
            <td><?php echo htmlspecialchars($row['primary_email']); ?></td>
            <td><?php echo htmlspecialchars($row['specialization']); ?></td>
            <td><?php echo htmlspecialchars($row['h_index']); ?></td>
            <td><?php echo htmlspecialchars($row['total_downloads']); ?></td>
        </tr>
    <?php } ?>
</table>

<?php include 'footer.php'; ?>