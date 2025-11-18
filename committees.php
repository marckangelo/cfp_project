<?php
session_start();
require 'db.php';
include 'header.php';


echo "<h2>Committees</h2>";
$query = "SELECT id, name, description FROM committees";
$commity = mysqli_query($conn, $query);

if (mysqli_num_rows($commity) > 0) {
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($commity)) {
        echo "<li>" . htmlspecialchars($row['name']);

        echo htmlspecialchars($row['description']);

        if ($_SESSION['member_id']) {
            echo " < form style='display:inline' method='POST
            action = 'request_comittee.php'>
            <input type= 'hidden' name='committee_id' value= ' " . $row['id'] . "'>
            <button type='submit'>JOIN</button>
            </form>";
        }
        echo "</li>";
    }
    echo "</lu>";
}
else {
    echo "<p> No committees available. </p>";
}

include 'footer.php'; ?>
