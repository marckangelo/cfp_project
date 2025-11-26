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

// Must have message_id in GET
if (!isset($_GET['message_id'])) {
    header("Location: messages_inbox.php");
    exit;
}

$message_id = (int) $_GET['message_id'];

// Load the message (only if it is addressed to this member)
$sql_message = "
    SELECT msg.*, 
           s.name AS sender_name,
           r.name AS recipient_name
    FROM message msg
    JOIN member s ON msg.sender_id   = s.member_id
    JOIN member r ON msg.recipient_id = r.member_id
    WHERE msg.message_id = $message_id
      AND msg.recipient_id = $member_id
";

$result_message = mysqli_query($conn, $sql_message);

if (!$result_message || mysqli_num_rows($result_message) === 0) {
    echo "<p>Message not found.</p>";
    include 'footer.php';
    exit;
}

$message = mysqli_fetch_assoc($result_message);

// Mark as read (if not already)
if ($message['is_read'] == 0) {
    $sql_mark_read = "
        UPDATE message
        SET is_read = 1
        WHERE message_id = $message_id
    ";
    mysqli_query($conn, $sql_mark_read);
}
?>

<h2>View Message</h2>

<!-- <strong> tag for bold characters -->
<p><strong>From:</strong> <?php echo htmlspecialchars($message['sender_name']); ?></p>
<p><strong>To:</strong>   <?php echo htmlspecialchars($message['recipient_name']); ?></p>
<p><strong>Sent At:</strong> <?php echo htmlspecialchars($message['sent_at']); ?></p>
<p><strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?></p>

<hr>

<p><?php echo nl2br(htmlspecialchars($message['body'])); ?></p>

<br>
<a href="messages_inbox.php">
    <button type="button">Back to Inbox</button>
</a>

<?php include 'footer.php'; ?>