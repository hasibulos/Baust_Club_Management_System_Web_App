<?php
session_start();
session_unset();
session_destroy();
header("Location: /BAUST_Club_Management_System/auth/login.php");
exit();
