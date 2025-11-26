<?php
session_start();
require 'db.php';
include 'header.php';

// User must be logged in as a member
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$member_id = (int) $_SESSION['member_id'];
$error_msg = "";

// ============= PROCESS FORM SUBMISSION =============
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Extract values from form
    $recipient_email = trim($_POST['recipient_email']);
    $subject         = trim($_POST['subject']);
    $body            = trim($_POST['body']);

    // Basic validation
    if ($recipient_email === "") {
        $error_msg = "Recipient email cannot be empty.";
    } else if (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } else if ($subject === "") {
        $error_msg = "Subject cannot be empty.";
    } else if ($body === "") {
        $error_msg = "Message body cannot be empty.";
    } else {

        // Escape email for SQL
        $recipient_email_sql = mysqli_real_escape_string($conn, $recipient_email);

        // Look up recipient in the member table by email
        $sql_find_recipient = "
            SELECT member_id, primary_email
            FROM member
            WHERE primary_email = '$recipient_email_sql'
            LIMIT 1
        ";

        $result_recipient = mysqli_query($conn, $sql_find_recipient);

        if (!$result_recipient || mysqli_num_rows($result_recipient) === 0) {
            // No member with this email
            $error_msg = "No member found with that email address.";
        } else {
            $row_recipient = mysqli_fetch_assoc($result_recipient);
            $recipient_id  = (int) $row_recipient['member_id'];

            // Make sure user is not messaging themselves
            if ($recipient_id === $member_id) {
                $error_msg = "You cannot send a message to yourself.";
            } else {
                // Escape subject and body for SQL
                $subject_sql = mysqli_real_escape_string($conn, $subject);
                $body_sql    = mysqli_real_escape_string($conn, $body);

                // Build INSERT query
                $sql_insert = "
                    INSERT INTO message (sender_id, recipient_id, subject, body, sent_at, is_read)
                    VALUES ($member_id, $recipient_id, '$subject_sql', '$body_sql', NOW(), 0)
                ";

                // Run query
                $result_insert = mysqli_query($conn, $sql_insert);

                if ($result_insert) {
                    $_SESSION['message_sent_success'] = "Message sent successfully.";
                    header("Location: messages_inbox.php");
                    exit;
                } else {
                    $error_msg = "Failed to send message. Please try again.";
                }
            }
        }
    }
}
?>

<h2>Send Message</h2>

<?php
// Show error if any
if ($error_msg !== "") {
    echo '<div style="color:red;">' . htmlspecialchars($error_msg) . '</div>';
}
?>

<form method="post" action="message_send.php">

    <label for="recipient_email">To (recipient email):</label><br>
    <input type="email" id="recipient_email" name="recipient_email" required><br><br>

    <label for="subject">Subject:</label><br>
    <input type="text" id="subject" name="subject" required><br><br>

    <label for="body">Message:</label><br>
    <textarea id="body" name="body" rows="6" cols="60" required></textarea><br><br>

    <button type="submit">Send Message</button>
</form>

<?php include 'footer.php'; ?>
