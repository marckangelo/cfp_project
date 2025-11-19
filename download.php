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
    $sql_country = "SELECT country 
                    FROM member 
                    WHERE member_id = $member_id";
    $result_country = mysqli_query($conn, $sql_country);
    $country_row = mysqli_fetch_assoc($result_country);
    $country = $country_row['country'];

    // *** MUST CHECK FOR DOWNLOAD LIMIT HERE BEFORE INSERT ***
    /*
    Logic: If download limit > 0 --> ok to download
           if download limit = 0 --> stop here, send SESSION data saying download limit reached and redirect to item.php or my_account.php

           (Done by fetching info using SQL queries)
    */

    // Fetching download_limit data of this current member
    $sql_download_limit = "SELECT download_limit
                                  FROM member
                                  WHERE member_id = $member_id";
    $result_download_limit = mysqli_query($conn, $sql_download_limit);
    $download_limit_row = mysqli_fetch_assoc($result_download_limit);
    $download_limit = $download_limit_row['download_limit'];

    if (downlad_limit > 0) {
        // Downloading the text item
        $sql_download = "INSERT INTO download (member_id, text_id, download_date, ip_address, user_agent, country)
                        VALUES ($member_id, $text_id, date('Y-m-d'), '$ip_address', '$user_agent', '$country')";

        $result_download = mysqli_query($conn, $sql_download);
    } else {
        
        $_SESSION['download_failure'] = "Download Failure. Download Limit reached.";

        // Head back to list of items/texts and display error message
        header("Location: my_account.php");
    }

    // *** MUST UPDATE THE DOWNLOAD LIMIT HERE (DECREMENT BY 1 FOR EACH DOWNLOAD) ***

    // If INSERT to download table was successful, save success message into SESSION
    if ($result_download) {
        $_SESSION['download_success'] = "Text Successfully Downloaded!";

        // UPDATE member's download_limit be decrementing by 1
        $sql_update_download_limit = "UPDATE member
                                  SET download_limit = download_limi - 1
                                  WHERE member_id = $member_id";
        $result_update_download_limit = mysqli_query($conn, $sql_update_download_limit);
    }

    // Head to the my_account.php page after successful download to see list of downloads
    header("Location: my_account.php");

} else {
    header("Location: login.php");
}
?>
