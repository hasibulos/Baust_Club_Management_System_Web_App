<!-- <?php
        require_once(__DIR__ . '/../includes/db.php');
        require_once(__DIR__ . '/../vendor/autoload.php'); // PHPMailer autoload

        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                $error = "Please enter your email.";
            } else {
                // Check if email exists
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    $error = "No account found with that email.";
                } else {
                    // Generate token
                    $reset_key = bin2hex(random_bytes(32));
                    $expDate = date("Y-m-d H:i:s", strtotime("+1 hour"));

                    // Insert token
                    $stmt_insert = $conn->prepare("INSERT INTO password_reset_temp (email, reset_key, expDate) VALUES (?, ?, ?)");
                    $stmt_insert->bind_param("sss", $email, $reset_key, $expDate);
                    $stmt_insert->execute();

                    // Reset link
                    $reset_link = "http://localhost/BAUST_Club_Management_System/auth/reset_password.php?key=$reset_key&email=$email";

                    // Send email via PHPMailer
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'brainiacacademics@gmail.com';     // ✅ Replace with your Gmail
                        $mail->Password   = 'dnuu fbxi cfqs wykw';       // ✅ Replace with Gmail App Password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // PHPMailer::ENCRYPTION_SMTPS
                        $mail->Port       = 465; // 465 for SMTPS

                        $mail->setFrom('brainiacacademics@gmail.com', 'BAUST Club Management System'); // ✅ Update sender name
                        $mail->addAddress($email);

                        $mail->isHTML(true);
                        $mail->Subject = 'Password Reset Request';
                        $mail->Body    = "
                    <h3>Password Reset Request</h3>
                    <p>Click the link below to reset your password:</p>
                    <p><a href='$reset_link' style='color:#256d6d;'>Reset Password</a></p>
                    <p>This link will expire in 1 hour.</p>
                ";
                        $mail->AltBody = "Click the link to reset your password: $reset_link";

                        $mail->send();
                        $success = "✅ Password reset link sent to your email.";
                    } catch (Exception $e) {
                        $error = "Email could not be sent. Error: SMTP authentication failed. Make sure you are using a Gmail App Password.";
                    }
                }
            }
        }
        ?>
<!DOCTYPE html>
<html>

<head>
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial;
            max-width: 400px;
            margin: 30px auto;
            padding: 20px;
            background-color: #f4f9f9;
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
    <h2>Forgot Password</h2>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Enter your email" required />
        <button type="submit">Send Reset Link</button>
    </form>
</body>

</html> -->