<?php
session_start();
require 'db.php';
if (isset($_SESSION['member_id'])) {
    //get the logged-in member's id and the committee id from the form submission
    $member_id = $_SESSION['member_id'];
    $committee_id = $_POST['committee_id'];
    $join_date = date('Y-m-d');
    $role = 'member';
    $status = $_POST['status'];
    $term_end_date = date('Y-m-d', strtotime('+1 year'));

    // Insert the join request into the committee_members table
   $sql_request = "INSERT INTO committee_membership 
                (member_id, committee_id, join_date, role, status, term_end_date)
                VALUES (
                    $member_id,
                    $committee_id,
                    '$join_date',
                    '$role',
                    '$status',
                    '$term_end_date'
                )";
    
    $result_request = mysqli_query($conn, $sql_request);
    // Redirect based on success or failure
    if ($result_request) {
        header("Location: my_account.php"); 
        exit;
    }
    else {
        header("Location: login.php");
        exit;
    }

}
// TODO: Insert committee join request for logged-in member


?>
