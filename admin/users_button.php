<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once('../db.php');

// Fixed department list
$departments = ['CSE', 'EEE', 'ME', 'IPE', 'English', 'Math', 'BBA', 'AIS'];

// Handle department filter
$selectedDept = isset($_GET['dept']) ? $_GET['dept'] : null;

if ($selectedDept && in_array($selectedDept, $departments)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE department = ? ORDER BY username ASC");
    $stmt->bind_param("s", $selectedDept);
    $stmt->execute();
    $users = $stmt->get_result();
    $userCount = $users->num_rows;
    $stmt->close();
} else {
    $users = $conn->query("SELECT * FROM users ORDER BY username ASC");
    $userCount = $users->num_rows;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>All Users</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 2rem;
        }

        h2 {
            color: #004d7a;
            margin-bottom: 1rem;
        }

        .filter-buttons {
            margin-bottom: 1.5rem;
        }

        .filter-buttons a {
            display: inline-block;
            margin: 0 8px 8px 0;
            padding: 8px 14px;
            background-color: #004d7a;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
        }

        .filter-buttons a.active {
            background-color: #14af96;
        }

        .user-count {
            margin-bottom: 1rem;
            font-weight: bold;
            color: #333;
        }

        .user-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .user-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 1rem;
        }

        .user-card h3 {
            margin: 0 0 0.5rem;
            color: #14af96;
        }

        .user-card p {
            margin: 0.3rem 0;
            color: #333;
        }

        .no-users {
            text-align: center;
            color: #999;
            font-style: italic;
            margin-top: 2rem;
        }
    </style>
</head>

<body>
    <h2>All Users</h2>

    <div class="filter-buttons">
        <a href="users_button.php" class="<?= !$selectedDept ? 'active' : '' ?>">All</a>
        <?php foreach ($departments as $dept): ?>
            <a href="users_button.php?dept=<?= urlencode($dept) ?>" class="<?= $selectedDept === $dept ? 'active' : '' ?>">
                <?= htmlspecialchars($dept) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="user-count">
        <?= $selectedDept ? "Total Users in " . htmlspecialchars($selectedDept) . ": $userCount" : "Total Users: $userCount" ?>
    </div>

    <?php if ($userCount > 0): ?>
        <div class="user-grid">
            <?php while ($user = $users->fetch_assoc()): ?>
                <div class="user-card">
                    <h3><?= htmlspecialchars($user['username']) ?></h3>
                    <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Department:</strong> <?= htmlspecialchars($user['department']) ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-users">No users found for this department.</div>
    <?php endif; ?>
</body>

</html>