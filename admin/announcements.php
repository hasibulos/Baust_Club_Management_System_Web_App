<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'SUPER_ADMIN') {
    header("Location: ../auth/login.php");
    exit();
}

require_once('../db.php');

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $posted_by = $_SESSION['username'];
    $role = $_SESSION['role'];

    $stmt = $conn->prepare("INSERT INTO announcements (title, content, posted_by, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $content, $posted_by, $role);
    $stmt->execute();
    header("Location: announcements.php");
    exit();
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete') {
    $id = $_POST['announcement_id'];
    $conn->query("DELETE FROM announcements WHERE id = $id");
    header("Location: announcements.php");
    exit();
}

// Handle mark/unmark
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['announcement_id']) && in_array($_POST['action'], ['mark', 'unmark'])) {
    $id = $_POST['announcement_id'];
    $isImportant = ($_POST['action'] === 'mark') ? 1 : 0;
    $conn->query("UPDATE announcements SET is_important = $isImportant WHERE id = $id");
    header("Location: announcements.php");
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    $id = $_POST['announcement_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $content, $id);
    $stmt->execute();
    header("Location: announcements.php");
    exit();
}

// Fetch all
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Announcements</title>
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

        .announcement,
        .add-form {
            background: #fff;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .announcement h3 {
            margin: 0 0 0.5rem;
            color: #14af96;
        }

        .announcement p,
        .announcement small {
            margin: 0.3rem 0;
            color: #333;
        }

        form {
            margin-top: 0.8rem;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            padding: 6px 12px;
            background-color: #004d7a;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            margin-right: 6px;
        }

        button:hover {
            background-color: #00385a;
        }
    </style>
</head>

<body>
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    <h2>Post New Announcement</h2>
    <div class="add-form">
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <input type="text" name="title" placeholder="Title" required>
            <textarea name="content" placeholder="Write your announcement..." required></textarea>
            <button type="submit">Post</button>
        </form>
    </div>

    <h2>All Announcements</h2>
    <?php while ($row = $announcements->fetch_assoc()): ?>
        <div class="announcement">
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="announcement_id" value="<?= $row['id'] ?>">
                <input type="text" name="title" value="<?= htmlspecialchars($row['title']) ?>" required>
                <textarea name="content" required><?= htmlspecialchars($row['content']) ?></textarea>
                <small>Posted by <?= htmlspecialchars($row['posted_by']) ?> on <?= $row['created_at'] ?></small><br><br>
                <button type="submit">Update</button>
            </form>

            <form method="POST" style="display:inline;">
                <input type="hidden" name="announcement_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button type="submit" onclick="return confirm('Delete this announcement?')">Delete</button>
            </form>

            <form method="POST" style="display:inline;">
                <input type="hidden" name="announcement_id" value="<?= $row['id'] ?>">
                <?php if ($row['is_important']): ?>
                    <button type="submit" name="action" value="unmark">Unmark Important</button>
                <?php else: ?>
                    <button type="submit" name="action" value="mark">Marks as Important</button>
                <?php endif; ?>
            </form>
        </div>
    <?php endwhile; ?>
</body>

</html>