<?php
require_once('../includes/session_check.php'); // পাথ চেক করে নিন
require_once('../includes/db.php');

// ১. সিকিউরিটি চেক: শুধু ক্লাব অ্যাডমিন ঢুকতে পারবে
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'CLUB_ADMIN') {
    header("Location: ../auth/login.php");
    exit();
}

// ২. সেশন থেকে এই অ্যাডমিনের ক্লাবের নাম নেওয়া
$current_club = $_SESSION['club_name'];

// ৩. শুধুমাত্র এই ক্লাবের ইভেন্টগুলো ফেচ করা
$stmt = $conn->prepare("SELECT * FROM events WHERE club_name = ? ORDER BY event_date DESC");
$stmt->bind_param("s", $current_club);
$stmt->execute();
$events = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($current_club) ?> Events</title>
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
            background-color: #004d7a;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
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
            padding: 1.5rem;
            border-top: 4px solid #14af96;
        }

        .event-card h3 {
            margin: 0 0 0.5rem;
            color: #14af96;
        }

        .event-card p {
            margin: 0.3rem 0;
            color: #333;
            font-size: 0.9rem;
        }

        .no-data {
            grid-column: 1 / -1;
            text-align: center;
            color: #999;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    <h2>Events of <?= htmlspecialchars($current_club) ?></h2>

    <div class="event-grid">
        <?php if ($events->num_rows > 0): ?>
            <?php while ($event = $events->fetch_assoc()): ?>
                <div class="event-card">
                    <h3><?= htmlspecialchars($event['title']) ?></h3>
                    <p><strong>📅 Date:</strong> <?= date('d M, Y', strtotime($event['event_date'])) ?></p>
                    <p><strong>📝 Description:</strong><br> <?= nl2br(htmlspecialchars($event['description'])) ?></p>

                    <?php if (!empty($event['link'])): ?>
                        <p><a href="<?= htmlspecialchars($event['link']) ?>" target="_blank" style="color: #004d7a;">View Link</a></p>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-data">
                <p>No events found for your club.</p>
                <a href="manage_events.php" style="color: #14af96;">Add your first event!</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>