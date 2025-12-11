<?php
/*
- Marck Angelo GELI (40265711)
- Arshdeep SINGH (40286514)
- Muhammad Adnan SHAHZAD (40282531)
- Muhammad RAZA (40284058)
*/

/*
Contributor to this file:
- Marck Angelo Geli (40265711)
- Muhammad RAZA (40284058)
*/

session_start();
require 'db.php';

// TODO: Check login, enforce download rules, log the download and serve the file

if (isset($_SESSION['member_id'])) {

    // Retrieve data to insert into download table
    $member_id  = (int) $_SESSION['member_id'];
    $text_id    = isset($_POST['text_id']) ? (int) $_POST['text_id'] : 0;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    if ($text_id <= 0) {
        $_SESSION['download_failure'] = "Invalid text selected for download.";
        header("Location: my_account.php");
        exit;
    }

    // Get country data from member table
    $sql_country = "SELECT country 
                    FROM member 
                    WHERE member_id = $member_id";
    $result_country = mysqli_query($conn, $sql_country);
    $country_row = mysqli_fetch_assoc($result_country);
    $country = $country_row ? $country_row['country'] : NULL;

    /*
        ================= DOWNLOAD LIMIT LOGIC =================
        Rule:
        - If member has donated in the last year -> max 1 download per DAY
        - Otherwise -> max 1 download per 7 DAYS
    */

    // 1) Check if this member has donated in the last year
    $sql_recent_donation = "
        SELECT COUNT(*) AS donation_count
        FROM donation
        WHERE member_id = $member_id
          AND date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    ";
    $result_recent_donation = mysqli_query($conn, $sql_recent_donation);
    $row_donation = mysqli_fetch_assoc($result_recent_donation);
    $has_recent_donation = ($row_donation && $row_donation['donation_count'] > 0);

    // 2) Decide the time window based on donation status
    if ($has_recent_donation) {
        // More generous rule: 1 download per day
        $interval_sql = "INTERVAL 1 DAY";
    } else {
        // Default rule: 1 download per week
        $interval_sql = "INTERVAL 7 DAY";
    }

    // 3) Check if they already downloaded something in that window
    //    (we limit total downloads, not per-text)
    $sql_recent_download = "
        SELECT COUNT(*) AS dl_count
        FROM download
        WHERE member_id = $member_id
          AND download_date >= DATE_SUB(NOW(), $interval_sql)
    ";
    $result_recent_download = mysqli_query($conn, $sql_recent_download);
    $row_download = mysqli_fetch_assoc($result_recent_download);
    $has_recent_download = ($row_download && $row_download['dl_count'] > 0);

    // 4) If they already downloaded in the window, block this download
    if ($has_recent_download) {

        if ($has_recent_donation) {
            // Donor rule message
            $_SESSION['download_failure'] =
                "Download limit reached: with a recent donation, you may download one text per day.";
        } else {
            // Non-donor rule message
            $_SESSION['download_failure'] =
                "Download limit reached: you may download one text per week. "
                . "Consider donating to unlock one download per day.";
        }

        // Head back to list of items/texts and display error message
        header("Location: my_account.php");
        exit;
    }

    // 5) If we reach here, the member is allowed to download now.
    //    Insert a row into download table.

    // Escape IP and user_agent data for safety (characters that need escaping safety)
    $ip_sql  = mysqli_real_escape_string($conn, $ip_address);
    $ua_sql  = mysqli_real_escape_string($conn, $user_agent);
    $country_sql = $country !== NULL ? ("'" . mysqli_real_escape_string($conn, $country) . "'") : "NULL";

    $sql_download = "
        INSERT INTO download (member_id, text_id, download_date, ip_address, user_agent, country)
        VALUES ($member_id, $text_id, NOW(), '$ip_sql', '$ua_sql', $country_sql)
    ";

    $result_download = mysqli_query($conn, $sql_download);

    // If INSERT to download table was successful, save success message into SESSION
    if ($result_download) {

        $_SESSION['download_success'] = "Text successfully downloaded!";

        // Update user download limit to 1 if download is allowed and 0 if not allowed 
        // (based on time constraint rules on download limit)
        $sql_update_download_limit = "
            UPDATE member
            SET download_limit = 0
            WHERE member_id = $member_id
        ";
        mysqli_query($conn, $sql_update_download_limit);

    } else {
        $_SESSION['download_failure'] = "Download failure. Please try again later.";
    }

    // Head to the my_account.php page after attempting download
    header("Location: my_account.php");
    exit;

} else {
    header("Location: login.php");
    exit;
}
?>
