<?php
function require_role(array $allowed_roles)
{
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: /BAUST_Club_Management_System/auth/login.php");
        exit();
    }
}
