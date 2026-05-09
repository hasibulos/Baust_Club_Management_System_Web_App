<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'DEPARTMENT_ADMIN') {
    header("Location: ../auth/login.php");
    exit();
}

require_once('../db.php');

// Get department from session
$department = $_SESSION['department'];

// Fetch events only for clubs in this department
$stmt = $conn->prepare("
    SELECT e.* FROM events e
    INNER JOIN clubs c ON e.club_name = c.name
    WHERE c.department = ?
    ORDER BY e.event_date DESC
");
$stmt->bind_param("s", $department);
$stmt->execute();
$events = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Department Events</title>
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
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    <h2>Events in <?= htmlspecialchars($department) ?> Department</h2>

    <div class="event-grid">
        <?php if ($events->num_rows > 0): ?>
            <?php while ($event = $events->fetch_assoc()): ?>
                <div class="event-card">
                    <h3><?= htmlspecialchars($event['title']) ?></h3>
                    <p><strong>Date:</strong> <?= htmlspecialchars($event['event_date']) ?></p>
                    <p><strong>Club:</strong> <?= htmlspecialchars($event['club_name']) ?></p>
                    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($event['description'])) ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; color:#999;">No events found for your department.</p>
        <?php endif; ?>
    </div>
</body>

</html>