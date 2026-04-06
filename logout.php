<?php
session_start();
session_destroy();
header("Location: /ClassSync/login.php?success=logout");
exit();
?>
