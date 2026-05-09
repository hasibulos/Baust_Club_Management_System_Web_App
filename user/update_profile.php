<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once('../db.php');

if (isset($_POST['update_profile'])) {
    $username = $_SESSION['username'];
    $student_id = trim($_POST['student_id']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);

    // Handle profile pic upload jodi thake
    $profile_pic = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        $fileName = basename($_FILES['profile_pic']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowed)) {
            $newFileName = $username . '_' . time() . '.' . $fileExt;
            $uploadFile = $uploadDir . $newFileName;
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadFile)) {
                $profile_pic = $newFileName;
            } else {
                die("Failed to upload profile picture.");
            }
        } else {
            die("Invalid file type for profile picture.");
        }
    }

    if ($profile_pic) {
        $stmt = $conn->prepare("UPDATE users SET student_id = ?, email = ?, department = ?, profile_pic = ? WHERE username = ?");
        $stmt->bind_param("sssss", $student_id, $email, $department, $profile_pic, $username);
    } else {
        $stmt = $conn->prepare("UPDATE users SET student_id = ?, email = ?, department = ? WHERE username = ?");
        $stmt->bind_param("ssss", $student_id, $email, $department, $username);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: profile.php");
        exit();
    } else {
        echo "Failed to update profile: " . $conn->error;
    }
}
