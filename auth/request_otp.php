<?php
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../vendor/autoload.php'); // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';
$step = 'email';
$email = '';
$otp = '';
$showResetForm = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'] ?? 'email';

    if ($step === 'email') {
        $email = strtolower(trim($_POST['email'] ?? '')); // ✅ always lowercase
        if (empty($email)) {
            $error = "Please enter your email.";
        } else {
            $stmt = $conn->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $exists = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if (!$exists) {
                $error = "No account found with that email.";
            } else {
                $conn->query("DELETE FROM password_reset_otp WHERE email = '$email'");
                $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

                // ✅ expiry handled by MySQL
                $stmt_insert = $conn->prepare("
                    INSERT INTO password_reset_otp (email, otp, expDate)
                    VALUES (?, ?, TIMESTAMPADD(MINUTE, 10, NOW()))
                ");
                $stmt_insert->bind_param("ss", $email, $otp);
                $stmt_insert->execute();
                $stmt_insert->close();

                // ✅ Send OTP via email
                $mail = new PHPMailer(true);
                try {
                    // $mail->SMTPDebug = 2;
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'brainiacacademics@gmail.com';
                    $mail->Password   = 'dnuu fbxi cfqs wykw'; // Gmail App Password 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // PHPMailer::ENCRYPTION_SMTPS
                    $mail->Port       = 465; // 465 for SMTPS

                    $mail->setFrom('brainiacacademics@gmail.com', 'BAUST Club Management System');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Your Password Reset OTP';
                    $mail->Body    = "
                        <h3>Password Reset OTP</h3>
                        <p>Your OTP for password reset is: <b>$otp</b></p>
                        <p>This OTP will expire in 10 minutes.</p>
                    ";
                    $mail->AltBody = "Your OTP is $otp. It will expire in 10 minutes.";

                    $mail->send();
                    $success = "OTP sent to your email.";
                    $step = 'otp';
                } catch (Exception $e) {
                    $error = "❌ OTP email failed. Check SMTP settings.";
                    $step = 'email';
                }
            }
        }
    } elseif ($step === 'otp') {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $otp   = trim($_POST['otp'] ?? ''); // ✅ keep leading zeros

        $stmt = $conn->prepare("
            SELECT 1
            FROM password_reset_otp
            WHERE otp = ?
              AND email = ?
              AND expDate >= NOW()
            LIMIT 1
        ");
        $stmt->bind_param("ss", $otp, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows === 0) {
            $error = "Invalid or expired OTP.";
            $step = 'otp';
        } else {
            $step = 'reset';
            $showResetForm = true;
        }
    } elseif ($step === 'reset') {
        $email      = strtolower(trim($_POST['email'] ?? ''));
        $userid     = trim($_POST['userid'] ?? '');
        $password_1 = $_POST['password_1'] ?? '';
        $password_2 = $_POST['password_2'] ?? '';

        if (empty($userid) || empty($password_1) || empty($password_2)) {
            $error = "All fields are required.";
            $step = 'reset';
            $showResetForm = true;
        } elseif (strlen($password_1) < 6) {
            $error = "Password must be at least 6 characters.";
            $step = 'reset';
            $showResetForm = true;
        } elseif ($password_1 !== $password_2) {
            $error = "Passwords do not match.";
            $step = 'reset';
            $showResetForm = true;
        } else {
            $password_hash = password_hash($password_1, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? AND username = ?");
            $stmt->bind_param("sss", $password_hash, $email, $userid);
            if ($stmt->execute()) {
                $success = "✅ Password reset successful. <a href='login.php'>Login here</a>";
                $conn->query("DELETE FROM password_reset_otp WHERE email = '$email'");
                $step = 'done';
            } else {
                $error = "Password reset failed.";
                $step = 'reset';
                $showResetForm = true;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Forgot Password (OTP)</title>
    <style>
        body {
            font-family: Arial;
            max-width: 420px;
            margin: 30px auto;
            padding: 20px;
            background: #f4f9f9;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #256d6d;
            margin-bottom: 20px;
        }

        input,
        button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            font-size: 1rem;
        }

        button {
            background-color: #256d6d;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #149494;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .success {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <h2>Forgot Password (OTP)</h2>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>

    <?php if ($step === 'email'): ?>
        <form method="post">
            <input type="hidden" name="step" value="email" />
            <input type="email" name="email" placeholder="Enter your email" required />
            <button type="submit">Send OTP</button>
        </form>

    <?php elseif ($step === 'otp'): ?>
        <form method="post">
            <input type="hidden" name="step" value="otp" />
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>" />
            <input type="text" name="otp" placeholder="Enter OTP" required />
            <button type="submit">Verify OTP</button>
        </form>

    <?php elseif ($step === 'reset' && $showResetForm): ?>
        <form method="post">
            <input type="hidden" name="step" value="reset" />
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>" />
            <input type="text" name="userid" placeholder="Enter your User name" required />
            <input type="password" name="password_1" placeholder="New Password" required />
            <input type="password" name="password_2" placeholder="Confirm Password" required />
            <button type="submit">Reset Password</button>
        </form>
    <?php endif; ?>
</body>

</html>