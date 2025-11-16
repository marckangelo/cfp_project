<?php
session_start();
require 'db.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: Insert new message into database
}
?>
<h2>Send Message</h2>
<p>TODO: Implement form to send a message to a member.</p>
<?php include 'footer.php'; ?>
