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
    $recipient_id = (int) $_POST['recipient_id'];
    $subject      = trim($_POST['subject']);
    $body         = trim($_POST['body']);

    // Basic validation
    if ($recipient_id <= 0 || $recipient_id == $member_id) {
        $error_msg = "Please select a valid recipient.";
    } else if ($subject === "") {
        $error_msg = "Subject cannot be empty.";
    } else if ($body === "") {
        $error_msg = "Message body cannot be empty.";
    } else {
        // Escape for SQL
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

// ============= LOAD LIST OF POSSIBLE RECIPIENTS =============
$sql_members = "SELECT member_id, name FROM member ORDER BY name";
$result_members = mysqli_query($conn, $sql_members);
?>

<h2>Send Message</h2>

<?php
// Show error if any
if ($error_msg !== "") {
    echo '<div style="color:red;">' . htmlspecialchars($error_msg) . '</div>';
}
?>

<form method="post" action="message_send.php">

    <label for="recipient_id">To (member):</label><br>
    <select id="recipient_id" name="recipient_id" required>
        <option value="">-- Select Recipient --</option>
        <?php
        if ($result_members) {
            while ($row = mysqli_fetch_assoc($result_members)) {
                // Do not allow sending to self
                if ((int)$row['member_id'] === $member_id) {
                    continue;
                }

                echo '<option value="' . (int)$row['member_id'] . '">'
                    . htmlspecialchars($row['name']) .
                    '</option>';
            }
        }
        ?>
    </select><br><br>

    <label for="subject">Subject:</label><br>
    <input type="text" id="subject" name="subject" required><br><br>

    <label for="body">Message:</label><br>
    <textarea id="body" name="body" rows="6" cols="60" required></textarea><br><br>

    <button type="submit">Send Message</button>
</form>

<?php include 'footer.php'; ?>
