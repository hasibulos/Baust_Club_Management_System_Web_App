<?php
require_once('../includes/session_check.php');
require_once('../includes/role_check.php');
require_role(['SUPER_ADMIN']);
require_once('../includes/db.php');

// Summary counts
$total_dept = $conn->query("SELECT COUNT(*) FROM departments")->fetch_row()[0];
$total_clubs = $conn->query("SELECT COUNT(*) FROM clubs")->fetch_row()[0];
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$total_events = $conn->query("SELECT COUNT(*) FROM events")->fetch_row()[0];

// Club moderators
$moderators = $conn->query("SELECT moderator, name AS club, department FROM clubs");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Super Admin Dashboard - BCMS</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f4f4f4;
            color: #333;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 240px;
            background: #004d7a;
            color: #fff;
            padding: 20px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 1.4rem;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            display: block;
        }

        .sidebar ul li a:hover {
            color: #1de9b6;
        }

        .sidebar .buttons {
            margin-top: 30px;
        }

        .sidebar .buttons a {
            display: block;
            background: #1de9b6;
            color: #004d7a;
            text-align: center;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .sidebar .buttons a:hover {
            background: #14af96;
            color: #fff;
        }

        .dashboard {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .top-section {
            padding: 30px;
            flex-shrink: 0;
        }

        .top-section h1 {
            margin-bottom: 20px;
        }

        .stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .card {
            background: #1de9b6;
            color: #004d7a;
            padding: 20px;
            border-radius: 10px;
            font-size: 1.2rem;
            text-align: center;
            flex: 1 1 200px;
        }

        .card span {
            display: block;
            font-size: 2rem;
            font-weight: bold;
            margin-top: 10px;
        }

        .scroll-section {
            padding: 30px;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table th,
        table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        table th {
            background: #004d7a;
            color: #fff;
        }

        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .actions div {
            background: #eee;
            padding: 20px;
            border-radius: 10px;
        }

        .actions h3 {
            margin-bottom: 10px;
        }

        .actions a {
            display: inline-block;
            background: #004d7a;
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
        }

        .actions a:hover {
            background: #1de9b6;
            color: #004d7a;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div>
                <h2>Admin Panel</h2>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage_department.php">Departments</a></li>
                    <li><a href="clubs_button.php">Clubs</a></li>
                    <li><a href="users_button.php">Users</a></li>
                    <li><a href="events_button.php">Events</a></li>
                    <li><a href="../user/home.php">View Site</a></li>
                    <li><a href="../auth/logout.php">Logout</a></li>
                </ul>
            </div>
            <div class="buttons">
                <a href="manage_clubs.php">Manage Clubs</a>
                <a href="manage_events.php">Manage Events</a>
                <a href="manage_admins.php">Manage Admins</a>
                <a href="manage_alumni.php">Manage Alumni</a>

                <a href="announcements.php">Announcements</a>
            </div>
        </aside>

        <!-- Main Dashboard -->
        <div class="dashboard">
            <div class="top-section">
                <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> 👑</h1>
                <div class="stats">
                    <div class="card">Departments<br><span><?= $total_dept ?></span></div>
                    <div class="card">Clubs<br><span><?= $total_clubs ?></span></div>
                    <div class="card">Users<br><span><?= $total_users ?></span></div>
                    <div class="card">Events<br><span><?= $total_events ?></span></div>
                </div>
            </div>

            <div class="scroll-section">
                <h2>Club Moderators</h2>
                <div style="max-height:400px; overflow-y:auto; border:1px solid #ccc; border-radius:8px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="position: sticky; top: 0; background: #004d7a; color: #fff; z-index: 1;">
                                <th style="padding:10px; border:1px solid #ccc;">Moderator</th>
                                <th style="padding:10px; border:1px solid #ccc;">Club</th>
                                <th style="padding:10px; border:1px solid #ccc;">Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $moderators->fetch_assoc()) { ?>
                                <tr>
                                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($row['moderator']) ?></td>
                                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($row['club']) ?></td>
                                    <td style="padding:10px; border:1px solid #ccc;"><?= htmlspecialchars($row['department']) ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    </div>
</body>

</html>