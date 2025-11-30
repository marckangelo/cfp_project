<?php
session_start();
require 'db.php';

if (isset($_SESSION['member_id'])) {

    // get the logged-in member's id and the committee id from the form submission
    $member_id = (int) $_SESSION['member_id'];
    $committee_id = (int) $_POST['committee_id'];
    $join_date = date('Y-m-d');
    $role = 'member';
    $status = $_POST['status'];
    $term_end_date = date('Y-m-d', strtotime('+1 year'));

    // Check if this member is already in this committee (any status)
    $sql_check = "
        SELECT 1
        FROM committee_membership
        WHERE member_id = $member_id
          AND committee_id = $committee_id
        LIMIT 1
    ";

    $result_check = mysqli_query($conn, $sql_check);

    if ($result_check && mysqli_num_rows($result_check) > 0) {
        // Already a member / already requested
        // (optional) set a message
        $_SESSION['committee_request_error'] = "You have already joined or requested to join this committee.";
        header("Location: committees.php");
        exit;
    }

    // 1) Insert the join request into the committee_membership table
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

    // 2) Update member_count for that committee
    $sql_member_update = "UPDATE committee 
                          SET member_count = member_count + 1 
                          WHERE committee_id = $committee_id";

    $result_request = mysqli_query($conn, $sql_request);

    // Redirect based on success or failure
    if ($result_request) {

        // run the UPDATE to increment member_count
        $result_member_update = mysqli_query($conn, $sql_member_update);

        header("Location: my_account.php"); 
        exit;
    } else {
        header("Location: login.php");
        exit;
    }
}

// TODO: Insert committee join request for logged-in member
?>
