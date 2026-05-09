<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'DEPARTMENT_ADMIN') {
    header("Location: ../auth/login.php");
    exit();
}

require_once('../db.php');

// Get department from session
$department = $_SESSION['department'];

// Fetch clubs only from this department
$stmt = $conn->prepare("SELECT * FROM clubs WHERE department = ? ORDER BY name ASC");
$stmt->bind_param("s", $department);
$stmt->execute();
$clubs = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Department Clubs</title>
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

        .club-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .club-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 1rem;
        }

        .club-card h3 {
            margin: 0 0 0.5rem;
            color: #14af96;
        }

        .club-card p {
            margin: 0.3rem 0;
            color: #333;
        }

        .club-card a {
            display: inline-block;
            margin-top: 0.8rem;
            padding: 6px 12px;
            background-color: #14af96;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
        }

        .club-card a:hover {
            background-color: #0b6d66;
        }
    </style>
</head>

<body>
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    <h2>Clubs in <?= htmlspecialchars($department) ?> Department</h2>

    <div class="club-grid">
        <?php if ($clubs->num_rows > 0): ?>
            <?php while ($club = $clubs->fetch_assoc()): ?>
                <div class="club-card">
                    <h3><?= htmlspecialchars($club['name']) ?></h3>
                    <p><strong>Type:</strong> <?= htmlspecialchars($club['type']) ?></p>
                    <p><strong>Department:</strong> <?= htmlspecialchars($club['department']) ?></p>
                    <p><strong>Moderator:</strong> <?= htmlspecialchars($club['moderator']) ?></p>
                    <?php if (!empty($club['website'])): ?>
                        <a href="<?= htmlspecialchars($club['website']) ?>" target="_blank">Visit Website</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; color:#999;">No clubs found for your department.</p>
        <?php endif; ?>
    </div>
</body>

</html>