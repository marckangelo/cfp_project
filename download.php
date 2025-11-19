<?php
session_start();
require 'db.php';

// TODO: Check login, enforce download rules, log the download and serve the file

if (isset($_SESSION['member_id'])) {

    // Retrieve data to insert into download table
    $member_id = $_SESSION['member_id'];
    $text_id = $_POST['text_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Get country data from member table
    $sql_country = "SELECT country FROM member WHERE member_id = $member_id";
    $result_country = mysqli_query($conn, $sql_country);
    $country_row = mysqli_fetch_assoc($result_country);
    $country = $country_row['country'];

    $sql_download = "INSERT INTO download (member_id, text_id, download_date, ip_address, user_agent, country)
                    VALUES ($member_id, $text_id, NOW(), '$ip_address', '$user_agent', '$country')";

    $result_download = mysqli_query($conn, $sql_download);

    if ($result_download) {
        $_SESSION['download_success'] = "Text Successfully Downloaded!";
    }

    // Head to the my_account.php page after successful download to see list of downloads
    header("Location: my_account.php");

} else {
    header("Location: login.php");
}
?>
