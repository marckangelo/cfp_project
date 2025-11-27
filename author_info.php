<?php
session_start();
require 'db.php';
include 'header.php';

// Check if 'orcid' parameter is provided
if (!isset($_GET['orcid'])) {
    echo "<p style='color:red;'>Missing author reference.</p>";
    include 'footer.php';
    exit;
}
// Sanitize input
$orcid = mysqli_real_escape_string($conn, $_GET['orcid']);
// Fetch author information
$sql_author = "SELECT a.orcid, m.name, a.bio, a.specialization, a.total_downloads 
               FROM author a
               JOIN member m ON a.member_id = m.member_id
               WHERE a.orcid = '$orcid'";
// Execute query
$result_author = mysqli_query($conn, $sql_author);
// Check if author exists
if (!$result_author || mysqli_num_rows($result_author) === 0) {
    echo "<p style='color:red;'>Author not found.</p>";
    include 'footer.php';
    exit;
}


?>
<h2>Author Information</h2>
<table border="1">
    <tr>
        <th>ORCID</th>
        <th>Name</th>
        <th>Bio</th>
        <th>Specialization</th>
        <th>Total Downloads</th>
    </tr>
    <?php 
    $row = mysqli_fetch_assoc($result_author);
    ?>
    <tr>
        <td><?php echo htmlspecialchars($row['orcid']); ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo nl2br(htmlspecialchars($row['bio'])); ?></td>
        <td><?php echo htmlspecialchars($row['specialization']); ?></td>
        <?php
        if ($row['total_downloads'] == null) {
            $row['total_downloads'] = 0;
        }
        ?>
        <td><?php echo htmlspecialchars($row['total_downloads']); ?></td>
    </tr>
</table>
