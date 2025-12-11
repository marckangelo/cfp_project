<?php
/*
- Marck Angelo GELI (40265711)
- Arshdeep SINGH (40286514)
- Muhammad Adnan SHAHZAD (40282531)
- Muhammad RAZA (40284058)
*/
session_start();
require 'db.php';

// Make sure user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$member_id = (int) $_SESSION['member_id'];

// ============= RECALCULATE UNREAD COUNT & UPDATE SESSION =============
$sql_unread = "
    SELECT COUNT(*) AS unread_count
    FROM message
    WHERE recipient_id = $member_id
      AND is_read = 0
";

$result_unread = mysqli_query($conn, $sql_unread);

if ($result_unread) {
    $row_unread = mysqli_fetch_assoc($result_unread);
    $unread_count = (int)$row_unread['unread_count'];

    $_SESSION['unread_count'] = $unread_count;
    $_SESSION['has_unread']   = ($unread_count > 0);

    // *** NOTE *** : Optional to show it once more when entering this INBOX page.

    // if ($unread_count == 0) {
    //     // No unread -> make sure popup doesn't trigger
    //     $_SESSION['unread_alert_shown'] = true;
    // } else {
    //     // There ARE unread messages -> allow popup on next page load
    //     unset($_SESSION['unread_alert_shown']);
    // }
}

include 'header.php'; // this include is here now so that the unread status before it runs header.php code

// ============= LOAD INBOX MESSAGES =============

// Build query
$sql_inbox = "
    SELECT msg.*, m.name AS sender_name
    FROM message msg
    JOIN member m
        ON msg.sender_id = m.member_id
    WHERE msg.recipient_id = $member_id
    ORDER BY msg.sent_at DESC
";

// Run query
$result_inbox = mysqli_query($conn, $sql_inbox);
?>

<h2>Inbox</h2>

<a href="message_send.php">
    <button type="button">Send New Message</button>
</a>
<br><br>

<?php
if ($result_inbox && mysqli_num_rows($result_inbox) > 0) {

    echo '
        <table border="1">
            <tr>
                <th>From</th>
                <th>Subject</th>
                <th>Sent At</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
    ';

    while ($row = mysqli_fetch_assoc($result_inbox)) {

        $status_label = ($row['is_read'] == 1) ? 'Read' : 'Unread';
        $message_id   = (int)$row['message_id'];

        echo '
            <tr>
                <td>' . htmlspecialchars($row['sender_name']) . '</td>
                <td>' . htmlspecialchars($row['subject']) . '</td>
                <td>' . htmlspecialchars($row['sent_at']) . '</td>
                <td>' . htmlspecialchars($status_label) . '</td>
                <td>
                    <form method="get" action="message_view.php">
                        <input type="hidden" name="message_id" value="' . $message_id . '">
                        <button type="submit">View</button>
                    </form>
                </td>
            </tr>
        ';
    }

    echo '</table>';

} else {
    echo '<p>No messages in your inbox.</p>';
}

include 'footer.php';
?>