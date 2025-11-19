<?php
session_start();
require 'db.php';

// TODO: Check login, enforce download rules, log the download and serve the file

if (isset($_SESSION['member_id'])) {

    

} else {
    header("Location: login.php");
}
?>
