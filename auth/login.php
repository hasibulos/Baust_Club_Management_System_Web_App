<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once('../db.php');
$error = '';
$login_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // === Fixed Super Admin Login ===
    if ($login_id === 'superadmin' && $password === '12345') {
        $_SESSION['username'] = 'superadmin';
        $_SESSION['role'] = 'SUPER_ADMIN';
        header("Location: ../admin/dashboard.php");
        exit();
    }

    if (empty($login_id) || empty($password)) {
        $error = "Username and Password are required!";
    } else {
        // ১. প্রথমে Admins টেবিল চেক করা (Super Admin, Dept Admin এর জন্য)
        $stmt = $conn->prepare("SELECT username, role, password, department FROM admins WHERE username = ? OR email = ? OR id = ?");
        $stmt->bind_param("sss", $login_id, $login_id, $login_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($dbUsername, $role, $dbPass, $dept);
            $stmt->fetch();

            if ($dbPass && password_verify($password, $dbPass)) {
                $_SESSION['username'] = $dbUsername;
                $_SESSION['role'] = $role;
                $_SESSION['department'] = $dept ?? null;
                $stmt->close();

                if ($role === 'SUPER_ADMIN') {
                    header("Location: ../admin/dashboard.php");
                } elseif ($role === 'DEPARTMENT_ADMIN') {
                    header("Location: ../department_admin/dashboard.php");
                } else {
                    header("Location: ../auth/welcome.php");
                }
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $stmt->close();

            // ২. অ্যাডমিন না হলে Users টেবিল চেক করা (Club Admin এবং সাধারণ স্টুডেন্টদের জন্য)
            $stmt2 = $conn->prepare("SELECT username, password, role, department, club_name FROM users WHERE username = ?");
            $stmt2->bind_param("s", $login_id);
            $stmt2->execute();
            $stmt2->store_result();

            if ($stmt2->num_rows === 1) {
                $stmt2->bind_result($userUsername, $userPass, $userRole, $userDept, $userClub);
                $stmt2->fetch();

                if ($userPass && password_verify($password, $userPass)) {
                    $_SESSION['username'] = $userUsername;
                    $_SESSION['role'] = $userRole; // ডাটাবেস থেকে প্রকৃত রোল নেওয়া (যেমন: CLUB_ADMIN)
                    $_SESSION['department'] = $userDept ?? null;
                    $_SESSION['club_name'] = $userClub ?? null;

                    $stmt2->close();

                    // রোল অনুযায়ী রিডাইরেক্ট
                    if ($userRole === 'CLUB_ADMIN') {
                        header("Location: ../club_admin/dashboard.php");
                    } else {
                        // যদি শুধু USER বা স্টুডেন্ট হয়
                        header("Location: ../auth/welcome.php");
                    }
                    exit();
                } else {
                    $error = "Invalid password!";
                }
            } else {
                $error = "Invalid username!";
            }
            $stmt2->close();
        }
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BAUST Club Management System - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        * {
            box-sizing: border-box;
        }

        body,
        html {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #051937, #004d7a);
            color: #fff;
            overflow: hidden;
        }

        #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #051937;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .spinner {
            border: 8px solid rgba(255, 255, 255, 0.3);
            border-top: 8px solid #1de9b6;
            border-radius: 50%;
            width: 70px;
            height: 70px;
            animation: spin 1.5s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .login-wrapper {
            display: none;
            max-width: 420px;
            width: 100%;
            background: rgba(0, 77, 122, 0.85);
            color: #1de9b6;
            padding: 3rem 4rem;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0, 77, 122, 0.9);
            text-align: center;
            user-select: none;
            margin: auto;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }

        .login-wrapper h1 {
            font-size: 2.6rem;
            font-weight: 700;
            margin-bottom: 2rem;
        }

        form {
            width: 100%;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 18px;
            margin-bottom: 1.6rem;
            font-size: 1rem;
            border-radius: 12px;
            border: 2px solid #1de9b6;
            font-weight: 500;
            transition: border-color 0.3s ease;
            outline: none;
            color: #fff;
            background-color: transparent;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #ffffff;
        }

        .password-container {
            position: relative;
            margin-bottom: 1.6rem;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #1de9b6;
            font-size: 1.2rem;
        }

        .btn {
            background-color: #1de9b6;
            color: #004d7a;
            padding: 14px 0;
            border: none;
            width: 100%;
            font-weight: 700;
            font-size: 1.1rem;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 12px 30px rgba(29, 233, 182, 0.7);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
        }

        .btn:hover {
            background-color: #14af96;
            color: #002d42;
            box-shadow: 0 15px 40px rgba(20, 174, 150, 0.8);
        }

        .error-message {
            color: #cc2f31;
            font-weight: 600;
            margin-bottom: 1.4rem;
        }

        .links {
            margin-top: 1rem;
            font-weight: 600;
            font-size: 1rem;
        }

        .links a {
            color: #1de9b6;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #14af96;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div id="loader">
        <div class="spinner"></div>
    </div>
    <div class="login-wrapper" id="loginWrapper">
        <h1>BAUST Club Management System</h1>
        <form method="post" action="">
            <input type="text" name="username" placeholder="Enter your username, student ID, or email" required value="<?php echo htmlspecialchars($login_id); ?>" />
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Enter your password" required />
                <i class="fa fa-eye toggle-password" id="togglePassword"></i>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <?php if (!empty($error)) : ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="links">
            <p><a href="request_otp.php">Forgot Password?</a></p>
            <p>No account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            const loader = document.getElementById('loader');
            const loginWrapper = document.getElementById('loginWrapper');
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
                loginWrapper.style.display = 'block';
            }, 500);
        });

        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>