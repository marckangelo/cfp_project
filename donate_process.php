<?php
session_start();
require 'db.php';

// TODO: Process donation form, insert into database, enforce allocation rules

if (isset($_SESSION['member_id'])) {

// CODE 

// Extract data to insert into database

//Validate the data

    // If data is valid build SQL query to insert


    // if statements to validate query
        // if Queries are good, head back to proper page after donating and display green success message on that page.


} else {
    header("Location: login.php");
    exit;
}
?>
