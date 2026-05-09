<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once('../db.php');
$error = '';
$success = '';
$student_id = '';
$username = '';
$email = '';
$department = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $password_1 = $_POST['password_1'] ?? '';
    $password_2 = $_POST['password_2'] ?? '';

    if (empty($student_id) || empty($username) || empty($email) || empty($department) || empty($password_1) || empty($password_2)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif ($password_1 !== $password_2) {
        $error = "Passwords do not match.";
    } else {
        $stmt_check = $conn->prepare("SELECT student_id FROM users WHERE student_id = ?");
        $stmt_check->bind_param("s", $student_id);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $error = "Student ID already registered.";
        }
        $stmt_check->close();

        if (!$error) {
            $stmt_check2 = $conn->prepare("SELECT username FROM users WHERE username = ?");
            $stmt_check2->bind_param("s", $username);
            $stmt_check2->execute();
            $stmt_check2->store_result();
            if ($stmt_check2->num_rows > 0) {
                $error = "Username already taken.";
            }
            $stmt_check2->close();
        }

        if (!$error) {
            $password_hash = password_hash($password_1, PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("INSERT INTO users (student_id, username, email, department, password, role) VALUES (?, ?, ?, ?, ?, 'USER')");
            $stmt_insert->bind_param("sssss", $student_id, $username, $email, $department, $password_hash);
            if ($stmt_insert->execute()) {
                $success = "Registration successful! <a href='login.php'>Click here to login</a>.";
                $student_id = $username = $email = $department = '';
            } else {
                $error = "Registration failed, please try again.";
            }
            $stmt_insert->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register - BAUST Club Management</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        .register-container {
            max-width: 520px;
            margin: 4rem auto;
            background: #fff;
            padding: 2.5rem 3.5rem;
            border-radius: 20px;
            box-shadow: 0 18px 36px rgba(25, 112, 106, 0.15);
        }

        .register-container h2 {
            color: #256d6d;
            font-weight: 700;
            font-size: 2.4rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-field {
            width: 100%;
            padding: 14px 18px;
            margin-bottom: 1.8rem;
            font-size: 1rem;
            border-radius: 12px;
            border: 2px solid #256d6d;
            font-weight: 500;
            transition: border-color 0.3s ease;
        }

        .form-field:focus {
            border-color: #149494;
            outline: none;
        }

        .btn {
            background-color: #149494;
            color: white;
            padding: 14px 0;
            border: none;
            width: 100%;
            font-weight: 700;
            font-size: 1.1rem;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(20, 148, 148, 0.35);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
        }

        .btn:hover {
            background-color: #0f7373;
            box-shadow: 0 12px 30px rgba(15, 115, 115, 0.55);
        }

        .message {
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .error-message {
            color: #cc2f31;
        }

        .success-message {
            color: #149494;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h2>Create an Account</h2>
        <?php if ($error): ?>
            <div class="message error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($success): ?>
            <div class="message success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <input type="text" name="student_id" placeholder="Student ID" value="<?php echo htmlspecialchars($student_id); ?>" class="form-field" required />
            <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>" class="form-field" required />
            <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" class="form-field" required />
            <input type="text" name="department" placeholder="Department" value="<?php echo htmlspecialchars($department); ?>" class="form-field" required />
            <input type="password" name="password_1" placeholder="Password" class="form-field" required />
            <input type="password" name="password_2" placeholder="Confirm Password" class="form-field" required />
            <button type="submit" class="btn">Register Now</button>
        </form>
        <button onclick="window.location.href='login.php'" class="btn" style="margin-top: 1.5rem;">Back to Login Page</button>
    </div>
</body>

</html>