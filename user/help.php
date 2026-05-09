<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Help / FAQ</title>
</head>

<body>
    <h2>Help & FAQ</h2>
    <p>Email: hasibulhasibofficial@gmail.com</p>
    <ul>
        <li>How to join?</li>
        <li>How to register for events?</li>
    </ul>
    <a href="home.php">Back to Home</a>
</body>

</html>