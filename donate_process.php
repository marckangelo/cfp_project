<?php
session_start();
require 'db.php';

// TODO: Process donation form, insert into database, enforce allocation rules

if (isset($_SESSION['member_id'])) {

// CODE HERE


} else {
    header("Location: login.php");
    exit;
}
?>
