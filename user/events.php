<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../auth/login.php');
    exit();
}
require_once('../db.php');

$result = $conn->query("SELECT * FROM events ORDER BY STR_TO_DATE(event_date, '%Y-%m-%d') ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Events - BAUST Club Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 2rem;
            background: linear-gradient(135deg, #051937, #004d7a);
            color: #eee;
            min-height: 100vh;
        }

        h2 {
            color: #1de9b6;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
        }

        .event-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }

        @media (max-width: 768px) {
            .event-grid {
                grid-template-columns: 1fr;
            }
        }

        event-card {
            display: flex;
            flex-direction: column;
            height: 400px;
            /* Fixed height */
            background: rgba(0, 77, 122, 0.9);
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 77, 122, 0.7);
            overflow: hidden;
            transition: transform 0.3s ease;
            cursor: pointer;
        }


        .event-card:hover {
            transform: scale(1.02);
        }

        .event-card img {
            flex: 7;
            width: 100%;
            object-fit: cover;
            object-position: center top;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .event-content {
            flex: 3;
            padding: 12px 15px;
            background: rgba(0, 77, 122, 0.95);
            color: #eee;
            overflow-y: auto;
        }


        .event-content h3 {
            margin: 0 0 10px;
            color: #1de9b6;
            font-size: 1.2rem;
        }

        .event-content p {
            margin: 5px 0;
            font-size: 0.95rem;
        }

        .event-content a {
            display: inline-block;
            margin-top: 10px;
            color: #bbeeff;
            text-decoration: none;
            font-weight: bold;
        }

        .event-content a:hover {
            text-decoration: underline;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: #1de9b6;
            font-weight: 600;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            background: rgba(29, 233, 182, 0.2);
            transition: background-color 0.3s ease;
        }

        .back-link:hover {
            background: rgba(29, 233, 182, 0.5);
        }
    </style>
</head>

<body>
    <h2>Upcoming Events</h2>
    <div class="event-grid">
        <?php while ($event = $result->fetch_assoc()) : ?>
            <div class="event-card" onclick="window.open('<?php echo htmlspecialchars($event['link']); ?>','_blank')">
                <?php if (!empty($event['image']) && file_exists($event['image'])): ?>
                    <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image">
                <?php else: ?>
                    <img src="../uploads/events/default_event.jpg" alt="Default Image">
                <?php endif; ?>
                <div class="event-content">
                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                    <p><strong>Club:</strong> <?php echo htmlspecialchars($event['club_name']); ?></p>
                    <p><strong>Department:</strong> <?php echo htmlspecialchars($event['department']); ?></p>
                    <p><?php echo htmlspecialchars($event['description']); ?></p>
                    <a href="<?php echo htmlspecialchars($event['link']); ?>" target="_blank">View More</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <a class="back-link" href="home.php">← Back to Home</a>
</body>

</html>