<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: auth/login.php');
    exit();
}

$devId = isset($_GET['dev']) ? intval($_GET['dev']) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>About Developer</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f8ff;
            color: #1e3a5f;
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            font-size: 2.2rem;
            color: #0b2447;
            margin-bottom: 10px;
        }

        p.subtitle {
            font-style: italic;
            font-size: 1.1rem;
            color: #3e6f8b;
            margin-bottom: 20px;
        }

        .dev-details {
            background: #d1e8e2;
            color: #0a3d62;
            border-radius: 10px;
            padding: 20px;
            font-size: 1.1rem;
            box-shadow: 0 0 12px rgba(20, 175, 150, 0.5);
            text-align: left;
        }

        a.back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 25px;
            font-weight: bold;
            font-size: 1.1rem;
            text-decoration: none;
            color: #0b2447;
            border: 3px solid #669bbc;
            border-radius: 25px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        a.back-link:hover {
            background-color: #669bbc;
            color: white;
        }
    </style>
</head>

<body>

    <?php if ($devId === 1): ?>
        <h2>About Hasibul Hasib</h2>
        <p class="subtitle">Pursuing B.Sc in CSE</p>
        <div class="dev-details">
            <strong>Hasibul Hasib</strong><br />
            Experience: 3 years in software development and project management.<br />
            Email: <a href="mailto:hasibul.hasib@example.com">hasibul.hasib@example.com</a>
        </div>
    <?php elseif ($devId === 2): ?>
        <h2>About Mst. Jugnu Khatun</h2>
        <p class="subtitle">Pursuing B.Sc in CSE</p>
        <div class="dev-details">
            <strong>Mst. Jugnu Khatun</strong><br />
            Experience: 2 years in web development and UI/UX design.<br />
            Email: <a href="mailto:jugnu.khatun@example.com">jugnu.khatun@example.com</a>
        </div>
    <?php elseif ($devId === 3): ?>
        <h2>About Most. Arifa Akter ALLo</h2>
        <p class="subtitle">Pursuing B.Sc in CSE</p>
        <div class="dev-details">
            <strong>Most. Arifa Akter ALLo</strong><br />
            Experience: 1 year in database administration and backend engineering.<br />
            Email: <a href="mailto:arifa.akter@example.com">arifa.akter@example.com</a>
        </div>
    <?php endif; ?>

    <a href="home.php" class="back-link">Back to Home</a>

</body>

</html>