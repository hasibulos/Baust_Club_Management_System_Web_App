<?php
require_once('../includes/session_check.php');
require_once('../includes/db.php');

// ১. সিকিউরিটি চেক: শুধু Club Admin ঢুকতে পারবে
if ($_SESSION['role'] !== 'CLUB_ADMIN') {
    header("Location: ../auth/login.php");
    exit();
}

$club_name = $_SESSION['club_name']; // এই অ্যাডমিন কোন ক্লাবের দায়িত্বে
$username = $_SESSION['username'];
$dept = $_SESSION['department'];

// ২. সামারি ডাটা (শুধু নির্দিষ্ট ক্লাবের জন্য)
// মেম্বার সংখ্যা
$total_members = $conn->query("SELECT COUNT(*) FROM club_members WHERE club_name = '$club_name'")->fetch_row()[0];
// ইভেন্ট সংখ্যা
$total_events = $conn->query("SELECT COUNT(*) FROM events WHERE club_name = '$club_name'")->fetch_row()[0];
// পেন্ডিং মেম্বারশিপ রিকোয়েস্ট (যদি থাকে)
$pending_requests = $conn->query("SELECT COUNT(*) FROM membership_requests WHERE club_name = '$club_name' AND status = 'PENDING'")->fetch_row()[0];

// ৩. রিসেন্ট ইভেন্ট লিস্ট আনা
$recent_events = $conn->prepare("SELECT title, event_date, status FROM events WHERE club_name = ? ORDER BY event_date DESC LIMIT 5");
$recent_events->bind_param("s", $club_name);
$recent_events->execute();
$eventsResult = $recent_events->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($club_name) ?> Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
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

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background: #002d42;
            /* একটু ডার্ক থিম */
            color: #fff;
            padding: 25px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            margin-bottom: 10px;
            font-size: 1.2rem;
            color: #1de9b6;
            border-bottom: 1px solid #1de9b6;
            padding-bottom: 10px;
        }

        .sidebar .club-tag {
            font-size: 0.8rem;
            background: #14af96;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 12px 0;
        }

        .sidebar ul li a {
            color: #bdc3c7;
            text-decoration: none;
            font-weight: 500;
            display: block;
            transition: 0.3s;
        }

        .sidebar ul li a:hover {
            color: #1de9b6;
            padding-left: 5px;
        }

        .sidebar .buttons {
            margin-top: 20px;
        }

        .sidebar .buttons a {
            display: block;
            background: #1de9b6;
            color: #004d7a;
            text-align: center;
            padding: 12px;
            margin-bottom: 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            font-size: 0.9rem;
        }

        .sidebar .buttons a:hover {
            background: #fff;
            transform: translateY(-2px);
        }

        /* Main Content */
        .dashboard {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .top-section {
            padding: 40px;
            background: #fff;
            border-bottom: 1px solid #eee;
        }

        .top-section h1 {
            margin: 0 0 25px 0;
            font-size: 1.8rem;
            color: #004d7a;
        }

        .stats {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .card {
            background: linear-gradient(135deg, #1de9b6, #14af96);
            color: #fff;
            padding: 25px;
            border-radius: 15px;
            min-width: 180px;
            flex: 1;
            box-shadow: 0 4px 15px rgba(29, 233, 182, 0.3);
            text-align: left;
            position: relative;
        }

        .card.pending {
            background: linear-gradient(135deg, #ff8a65, #e64a19);
            box-shadow: 0 4px 15px rgba(230, 74, 25, 0.3);
        }

        .card span {
            display: block;
            font-size: 2.2rem;
            font-weight: 800;
            margin-top: 5px;
        }

        /* Table Section */
        .scroll-section {
            padding: 40px;
            overflow-y: auto;
            background: #fdfdfd;
        }

        .table-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background: #f8f9fa;
            color: #004d7a;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .upcoming {
            background: #e3f2fd;
            color: #1976d2;
        }

        .completed {
            background: #e8f5e9;
            color: #2e7d32;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div>
                <h2>Club Admin</h2>
                <div class="club-tag"><?= htmlspecialchars($club_name) ?></div>
                <ul>
                    <li><a href="dashboard.php">📊 Dashboard</a></li>
                    <li><a href="club_members.php">👥 Members List</a></li>
                    <li><a href="club_events.php">📅 Club Events</a></li>
                    <li><a href="profile.php">👤 My Profile</a></li>
                    <li><a href="../user/home.php" target="_blank">🌐 View Website</a></li>
                    <li><a href="../auth/logout.php" style="color: #ff8a65;">🚪 Logout</a></li>
                </ul>
            </div>
            <div class="buttons">
                <a href="manage_members.php">Manage Members</a>
                <a href="add_event.php">Create New Event</a>
                <a href="manage_gallery.php">Club Gallery</a>
                <a href="membership_requests.php">Requests (<?= $pending_requests ?>)</a>
            </div>
        </aside>

        <!-- Main Dashboard -->
        <div class="dashboard">
            <div class="top-section">
                <h1>Welcome back, <?= htmlspecialchars($username) ?> 👋</h1>
                <div class="stats">
                    <div class="card">
                        Total Members
                        <span><?= $total_members ?></span>
                    </div>
                    <div class="card">
                        Total Events
                        <span><?= $total_events ?></span>
                    </div>
                    <div class="card pending">
                        Pending Requests
                        <span><?= $pending_requests ?></span>
                    </div>
                </div>
            </div>

            <div class="scroll-section">
                <div class="table-container">
                    <h2 style="margin-top: 0; color: #004d7a;">Recent Club Events</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Event Title</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($eventsResult->num_rows > 0): ?>
                                <?php while ($row = $eventsResult->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                                        <td><?= date('d M, Y', strtotime($row['event_date'])) ?></td>
                                        <td>
                                            <?php
                                            $statusClass = (strtotime($row['event_date']) >= time()) ? 'upcoming' : 'completed';
                                            $statusText = (strtotime($row['event_date']) >= time()) ? 'Upcoming' : 'Past Event';
                                            ?>
                                            <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align:center;">No events created yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>