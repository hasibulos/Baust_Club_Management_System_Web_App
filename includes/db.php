<?php
$host = "localhost";
$port = 3307; // apnar server er port number
$user = "root";
$password = "";
$dbname = "baust_club_management_system_v1";

// Host er sathe port specify kore connection
$conn = new mysqli($host, $user, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
