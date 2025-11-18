<?php
session_start();
require 'db.php';
include 'header.php';


echo "<h2>Committees</h2>";
//retreiving data
$query = "SELECT committee_id, name, purpose FROM committee";
$commity = mysqli_query($conn, $query);

//checks if there are any committees present 
if (mysqli_num_rows($commity) > 0) {
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($commity)) {
        echo "<li>" . htmlspecialchars($row['name']);

        echo htmlspecialchars($row['description']);
        //checks if a user is logged in 
        if ($_SESSION['member_id']) {
            echo " < form style='display:inline' method='POST
            action = 'request_comittee.php'>
            //silently sends the database the id of the committee
            <input type= 'hidden' name='committee_id' value= ' " . $row['id'] . "'>
            //displays a join button
            <button type='submit'>JOIN</button>
            </form>";
        }
        echo "</li>";
    }
    echo "</lu>";
}
//displays message 
else {
    echo "<p> No committees available. </p>";
}

include 'footer.php'; ?>
