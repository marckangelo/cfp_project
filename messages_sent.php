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

// ============= LOAD SENT MESSAGES =============

// Build query
$sql_sent = "
    SELECT msg.*, m.name AS recipient_name
    FROM message msg
    JOIN member m
        ON msg.recipient_id = m.member_id
    WHERE msg.sender_id = $member_id
    ORDER BY msg.sent_at DESC
";

// Run query
$result_sent = mysqli_query($conn, $sql_sent);

?>

<h2>Sent Messages</h2>

<a href="message_send.php">
    <button type="button">Send New Message</button>
</a>
<br><br>

<?php
if ($result_sent && mysqli_num_rows($result_sent) > 0) {

    echo '
        <table border="1">
            <tr>
                <th>To</th>
                <th>Subject</th>
                <th>Sent At</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
    ';

    while ($row = mysqli_fetch_assoc($result_sent)) {

        $status_label = ($row['is_read'] == 1) ? 'Read by recipient' : 'Unread by recipient';

        echo '
            <tr>
                <td>' . htmlspecialchars($row['recipient_name']) . '</td>
                <td>' . htmlspecialchars($row['subject']) . '</td>
                <td>' . htmlspecialchars($row['sent_at']) . '</td>
                <td>' . htmlspecialchars($status_label) . '</td>
                <td>
                    <form method="get" action="messages_view.php">
                        <input type="hidden" name="message_id" value="' . (int)$row['message_id'] . '">
                        <button type="submit">View</button>
                    </form>
                </td>
            </tr>
        ';
    }

    echo '</table>';

} else {
    echo '<p>You have not sent any messages yet.</p>';
}

include 'footer.php';
?>
