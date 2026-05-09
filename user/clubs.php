<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../auth/login.php');
    exit();
}
require_once('../db.php');

// Build filtering query
$filterQuery = "SELECT * FROM clubs";
$conditions = [];

if (isset($_GET['filter'])) {
    $filterType = $_GET['filter'];
    $filterValue = isset($_GET['value']) ? strtolower(trim($_GET['value'])) : '';

    if ($filterType === 'type' && $filterValue !== '') {
        $conditions[] = "LOWER(type) = '" . $conn->real_escape_string($filterValue) . "'";
    } elseif ($filterType === 'department' && $filterValue !== '') {
        $conditions[] = "LOWER(department) = '" . $conn->real_escape_string($filterValue) . "'";
    } elseif ($filterType === 'all') {
        // No condition needed — show all
    }
}

if (!empty($conditions)) {
    $filterQuery .= " WHERE " . implode(" AND ", $conditions);
}

$filterQuery .= " ORDER BY name ASC";
$clubs = $conn->query($filterQuery);

// Get departments for filter buttons
$departmentsResult = $conn->query("SELECT DISTINCT department FROM clubs ORDER BY department ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Clubs at BAUST</title>
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
            margin-bottom: 1rem;
        }

        .filter-bar {
            text-align: center;
            margin-bottom: 2rem;
        }

        .filter-bar a {
            background: #1de9b6;
            color: #004d7a;
            text-decoration: none;
            padding: 8px 14px;
            margin: 5px;
            border-radius: 6px;
            font-weight: bold;
            display: inline-block;
        }

        .filter-bar a:hover {
            background: #14af96;
        }

        .club-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        @media (max-width: 900px) {
            .club-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .club-grid {
                grid-template-columns: 1fr;
            }
        }

        .club-card {
            background: rgba(0, 77, 122, 0.9);
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 77, 122, 0.7);
            transition: transform 0.3s ease;
            display: grid;
            grid-template-rows: 70% 30%;
            height: 400px;
            overflow: hidden;
        }

        .club-card:hover {
            transform: scale(1.02);
        }

        .club-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
        }

        .club-content {
            padding: 12px 15px;
            background: rgba(0, 77, 122, 0.95);
            color: #eee;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .club-content h3 {
            margin: 0 0 10px;
            color: #1de9b6;
            font-size: 1.2rem;
        }

        .club-content p {
            margin: 5px 0;
            font-size: 0.95rem;
        }

        .join-btn {
            display: inline-block;
            margin-top: 10px;
            background: #1de9b6;
            color: #004d7a;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
        }

        .join-btn:hover {
            background: #14af96;
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
    <h2>Clubs at BAUST</h2>

    <div class="filter-bar">
        <a href="clubs.php?filter=all">All</a>
        <a href="clubs.php?filter=type&value=cultural">Cultural</a>
        <a href="clubs.php?filter=type&value=technical">Technical</a>
        <a href="clubs.php?filter=type&value=sports">Sports</a>
        <?php while ($dep = $departmentsResult->fetch_assoc()) : ?>
            <a href="clubs.php?filter=department&value=<?= strtolower($dep['department']) ?>">
                <?= htmlspecialchars($dep['department']) ?>
            </a>
        <?php endwhile; ?>
    </div>

    <div class="club-grid">
        <?php while ($club = $clubs->fetch_assoc()) : ?>
            <div class="club-card">
                <?php if (!empty($club['image']) && file_exists($club['image'])): ?>
                    <img src="<?= htmlspecialchars($club['image']) ?>" alt="Club Image">
                <?php else: ?>
                    <img src="../uploads/clubs/default_club.jpg" alt="Default Image">
                <?php endif; ?>

                <div class="club-content">
                    <h3><?= htmlspecialchars($club['name']) ?></h3>
                    <p><strong>Type:</strong> <?= htmlspecialchars($club['type']) ?></p>
                    <p><strong>Department:</strong> <?= htmlspecialchars($club['department']) ?></p>
                    <p><strong>Moderator/President:</strong> <?= htmlspecialchars($club['moderator']) ?></p>
                    <a href="join_request.php?club_id=<?= $club['id'] ?>" class="join-btn">Join Request</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <a class="back-link" href="home.php">← Back to Home</a>
</body>

</html>