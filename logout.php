<?php
session_start();
session_unset();
session_destroy();
header("Location: /tms/train-management-system/index.php");
exit;
?>
