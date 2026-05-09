<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Welcome - BAUST Club Management</title>
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #051937, #004d7a);
            font-family: 'Poppins', sans-serif;
            color: #fff;
            overflow: hidden;
        }

        .welcome-container {
            text-align: center;
            max-width: 600px;
            padding: 3rem 4rem;
            background: rgba(0, 77, 122, 0.85);
            border-radius: 25px;
            box-shadow: 0 0 30px rgba(0, 77, 122, 0.9);
            animation: fadeInScale 1.2s ease forwards;
        }

        h1 {
            margin-bottom: 1rem;
            font-size: 3.2rem;
            font-weight: 700;
            letter-spacing: 2px;
            animation: textGlow 2.5s infinite alternate;
        }

        p {
            font-size: 1.4rem;
            font-weight: 500;
            margin-bottom: 2rem;
        }

        .loader {
            margin: 0 auto;
            width: 60px;
            height: 60px;
            border: 7px solid rgba(255, 255, 255, 0.3);
            border-top-color: #1de9b6;
            border-radius: 50%;
            animation: spin 1.2s linear infinite;
        }

        @keyframes fadeInScale {
            0% {
                opacity: 0;
                transform: scale(0.75);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes textGlow {
            0% {
                text-shadow: 0 0 5px #1de9b6;
            }

            100% {
                text-shadow: 0 0 20px #1de9b6;
            }
        }
    </style>

    <script>
        setTimeout(() => {
            window.location.href = "../user/home.php";
        }, 4000); // 4000ms = 4 seconds
    </script>

</head>

<body>
    <div class="welcome-container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Your journey to club activities begins here.</p>
        <div class="loader" aria-label="Loading animation"></div>
    </div>
</body>

</html>