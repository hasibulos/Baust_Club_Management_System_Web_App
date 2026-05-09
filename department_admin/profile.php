<?php
require_once('../includes/session_check.php');
require_once('../includes/db.php');

// Get username from session
$username = $_SESSION['username'];

// Fetch admin info
$stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$id = $admin['id'];
$department = $admin['department'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    if (!empty($_FILES['profile_pic']['name'])) {
        $targetDir = "../uploads/profiles/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $targetFile = $targetDir . $fileName;
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile);
        $stmt = $conn->prepare("UPDATE admins SET name=?, email=?, profile_pic=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $email, $fileName, $id);
    } else {
        $stmt = $conn->prepare("UPDATE admins SET name=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $email, $id);
    }

    $stmt->execute();
    header("Location: profile.php");
    exit();
}

// Count clubs and events added by this admin
$total_clubs = $conn->query("SELECT COUNT(*) FROM clubs WHERE added_by = $id")->fetch_row()[0];
$total_events = $conn->query("SELECT COUNT(*) FROM events WHERE added_by = $id")->fetch_row()[0];

// Fetch clubs added by this admin
$clubList = $conn->query("SELECT name, website FROM clubs WHERE added_by = $id");

// Fetch click stats for each club
$clickStats = $conn->query("
    SELECT club_name, COUNT(*) AS clicks
    FROM club_clicks
    WHERE club_name IN (SELECT name FROM clubs WHERE added_by = $id)
    GROUP BY club_name
");
$clickMap = [];
while ($row = $clickStats->fetch_assoc()) {
    $clickMap[$row['club_name']] = $row['clicks'];
}

// Fetch users from admin's department
$deptUsers = $conn->prepare("SELECT username, email FROM users WHERE department = ?");
$deptUsers->bind_param("s", $department);
$deptUsers->execute();
$deptResult = $deptUsers->get_result();
$deptSummary = [];
while ($user = $deptResult->fetch_assoc()) {
    $deptSummary[] = [
        'username' => $user['username'],
        'email' => $user['email']
    ];
}

// Count total club and event clicks by department users
$clubClickCount = $conn->query("
    SELECT COUNT(*) FROM club_clicks 
    WHERE clicked_by IN (SELECT username FROM users WHERE department = '$department')
")->fetch_row()[0];

$eventClickCount = 0;
if ($conn->query("SHOW TABLES LIKE 'event_clicks'")->num_rows > 0) {
    $eventClickCount = $conn->query("
        SELECT COUNT(*) FROM event_clicks 
        WHERE clicked_by IN (SELECT username FROM users WHERE department = '$department')
    ")->fetch_row()[0];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 2rem;
        }

        .profile-container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .profile-header img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #14af96;
        }

        .profile-header h2 {
            margin: 0;
            color: #004d7a;
        }

        .back-btn {
            padding: 10px 20px;
            background-color: #004d7a;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        form input,
        form button {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 0.5rem 0 1rem 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        form button {
            background-color: #14af96;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }

        .stats {
            display: flex;
            gap: 2rem;
            margin: 2rem 0;
        }

        .stats div {
            background: #1de9b6;
            color: #004d7a;
            padding: 1rem;
            border-radius: 10px;
            flex: 1;
            text-align: center;
            font-weight: bold;
        }

        .club-list {
            margin-top: 2rem;
        }

        .club-list h3 {
            color: #004d7a;
            margin-bottom: 1rem;
        }

        .club-list ul {
            list-style: none;
            padding: 0;
        }

        .club-list li {
            background: #f9f9f9;
            padding: 0.8rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
        }

        .club-list a {
            color: #14af96;
            font-weight: bold;
            text-decoration: none;
        }

        .club-list small {
            color: #666;
            margin-left: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background: #14af96;
            color: white;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .edit-btn {
            margin-top: 10px;
            padding: 8px 16px;
            background: #14af96;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-info">
                <img src="<?= !empty($admin['profile_pic']) ? '../uploads/profiles/' . htmlspecialchars($admin['profile_pic']) : '../uploads/profiles/default.jpg' ?>" alt="Profile Picture">
                <div>
                    <h2><?= htmlspecialchars($admin['name']) ?></h2>
                    <p><?= htmlspecialchars($admin['email']) ?></p>
                    <button onclick="toggleEdit()" class="edit-btn">Edit Profile</button>
                </div>
            </div>
            <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>

        <form method="post" enctype="multipart/form-data" id="editForm" style="display:none;">
            <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" required />
            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required />
            <input <input type="file" name="profile_pic" accept="image/*" />
            <button type="submit">Update Profile</button>
        </form>

        <div class="stats">
            <div>Clubs Added<br><span><?= $total_clubs ?></span></div>
            <div>Events Added<br><span><?= $total_events ?></span></div>
        </div>

        <div class="club-list">
            <h3>Your Clubs</h3>
            <ul>
                <?php while ($club = $clubList->fetch_assoc()): ?>
                    <li>
                        <a href="<?= htmlspecialchars($club['website']) ?>" target="_blank"><?= htmlspecialchars($club['name']) ?></a>
                        <small>Clicks: <?= $clickMap[$club['name']] ?? 0 ?></small>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>

        <div class="club-list" style="margin-top:3rem;">
            <h3><?= htmlspecialchars($department) ?> Department Users</h3>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deptSummary as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="club-list" style="margin-top:2rem;">
            <button onclick="toggleDeptStats()" class="edit-btn">Show Department Click Summary</button>
            <div id="deptStats" style="display:none; margin-top:1rem;">
                <table>
                    <thead>
                        <tr>
                            <th>Click Type</th>
                            <th>Total Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Club Clicks</td>
                            <td><?= $clubClickCount ?></td>
                        </tr>
                        <tr>
                            <td>Event Clicks</td>
                            <td><?= $eventClickCount ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleEdit() {
            const form = document.getElementById('editForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function toggleDeptStats() {
            const stats = document.getElementById('deptStats');
            stats.style.display = stats.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>

</html>