<?php
session_start();
unset($_SESSION['admin_id']);
header("Location: admin_login.php");
exit();
?>