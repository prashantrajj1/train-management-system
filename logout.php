<?php
session_start();
session_unset();
session_destroy();
header("Location: /tms/index.php");
exit;
?>
