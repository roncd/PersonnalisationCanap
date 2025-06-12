<?php
session_start();
require '../../admin/include/session_expiration.php';
$_SESSION = array();
session_destroy();
header("Location: ../pages/index.php");
exit;
?>
