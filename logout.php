<?php
// Destroy all SESSION data when logging out

session_start();
session_unset();
session_destroy();
header('Location: index.php');
exit;