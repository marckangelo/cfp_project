<?php
session_start();
require 'db.php';
if ($_SESSION['member_id']) {
    echo "Only members may join committees.";
}
// TODO: Insert committee join request for logged-in member


?>
