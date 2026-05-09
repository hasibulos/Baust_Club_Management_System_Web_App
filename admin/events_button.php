<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once('../db.php');

// Fetch all events
$events = $conn->query("SELECT * FROM events ORDER BY event_date DESC");

// Determine dashboard path
$roleFolder = ($_SESSION['role'] === 'SUPER_ADMIN') ? 'super_admin' : ($_SESSION['role'] === 'DEPARTMENT_ADMIN' ? 'department_admin' : '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Events</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 2rem;
        }

        a.back-btn {
            display: inline-block;
            margin-bottom: 1.5rem;
            padding: 8px 16px;
            background-color: #14af96;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: #004d7a;
            margin-bottom: 1rem;
        }

        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .event-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 1rem;
        }

        .event-card h3 {
            margin: 0 0 0.5rem;
            color: #14af96;
        }

        .event-card p {
            margin: 0.3rem 0;
            color: #333;
        }
    </style>
</head>

<body>
    <?php if ($roleFolder): ?>
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a> <?php endif; ?>

    <h2>All Events</h2>
    <div class="event-grid">
        <?php while ($event = $events->fetch_assoc()): ?>
            <div class="event-card">
                <h3><?= htmlspecialchars($event['title']) ?></h3>
                <p><strong>Date:</strong> <?= htmlspecialchars($event['event_date']) ?></p>
                <p><strong>Club:</strong> <?= htmlspecialchars($event['club_name']) ?></p>
                <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($event['description'])) ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</body>

</html>