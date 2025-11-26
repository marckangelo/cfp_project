<?php
session_start();
require 'db.php';
include 'header.php';

// Make sure user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$member_id = (int) $_SESSION['member_id'];

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

<?php
if ($result_inbox && mysqli_num_rows($result_inbox) > 0) {

    echo '
        <table border="1">
            <tr>
                <th>From</th>
                <th>Subject</th>
                <th>Sent At</th>
                <th>Status</th>
            </tr>
    ';

    while ($row = mysqli_fetch_assoc($result_inbox)) {

        $status_label = ($row['is_read'] == 1) ? 'Read' : 'Unread';

        echo '
            <tr>
                <td>' . htmlspecialchars($row['sender_name']) . '</td>
                <td>' . htmlspecialchars($row['subject']) . '</td>
                <td>' . htmlspecialchars($row['sent_at']) . '</td>
                <td>' . htmlspecialchars($status_label) . '</td>
            </tr>
        ';
    }

    echo '</table>';

} else {
    echo '<p>No messages in your inbox.</p>';
}

include 'footer.php';
?>
