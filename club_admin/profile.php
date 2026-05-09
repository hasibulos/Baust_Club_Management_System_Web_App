<?php
require_once('../includes/session_check.php');
require_once('../includes/db.php');

// সিকিউরিটি চেক
if ($_SESSION['role'] !== 'CLUB_ADMIN') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// প্রোফাইল আপডেট লজিক
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $new_password = $_POST['password'];

    if (!empty($new_password)) {
        // পাসওয়ার্ড হ্যাশ করে আপডেট করা হচ্ছে
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $full_name, $hashed_password, $user_id);
    } else {
        // শুধু নাম আপডেট
        $stmt = $conn->prepare("UPDATE users SET full_name = ? WHERE id = ?");
        $stmt->bind_param("si", $full_name, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name; // সেশন আপডেট
        $success_msg = "Profile updated successfully!";
    } else {
        $error_msg = "Something went wrong. Please try again.";
    }
}

// বর্তমান ডাটা ফেচ করা
$stmt = $conn->prepare("SELECT username, full_name, role, club_name, department FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile - Club Admin</title>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f4f4; padding: 40px; }
        .profile-card { background: #fff; max-width: 500px; margin: auto; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { color: #004d7a; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        label { display: block; margin-top: 15px; font-weight: bold; color: #555; }
        input { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        input[readonly] { background: #f9f9f9; color: #888; }
        button { background: #004d7a; color: #fff; border: none; padding: 12px; width: 100%; border-radius: 5px; margin-top: 20px; cursor: pointer; }
        .msg { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .back-link { display: block; margin-top: 20px; text-align: center; color: #004d7a; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body>

    <div class="profile-card">
        <h2>My Profile</h2>

        <?php if($success_msg): ?> <div class="msg success"><?= $success_msg ?></div> <?php endif; ?>
        <?php if($error_msg): ?> <div class="msg error"><?= $error_msg ?></div> <?php endif; ?>

        <form method="post">
            <label>Username (Cannot Change)</label>
            <input type="text" value="<?= htmlspecialchars($user_data['username']) ?>" readonly>

            <label>Club Name</label>
            <input type="text" value="<?= htmlspecialchars($user_data['club_name']) ?>" readonly>

            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($user_data['full_name']) ?>" required>

            <label>New Password (Leave blank to keep current)</label>
            <input type="password" name="password" placeholder="Enter new password">

            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

</body>
</html>