<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: /BAUST_Club_Management_System/auth/login.php");
    exit();
}
