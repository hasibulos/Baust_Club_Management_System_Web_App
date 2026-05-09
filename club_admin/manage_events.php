<?php
require_once('../includes/session_check.php');
require_once('../includes/db.php');

// ১. সিকিউরিটি চেক: শুধু ক্লাব অ্যাডমিন ঢুকতে পারবে
if ($_SESSION['role'] !== 'CLUB_ADMIN') {
    header("Location: ../auth/login.php");
    exit();
}

// সেশন থেকে ডাটা নেওয়া
$current_club = $_SESSION['club_name']; // এই অ্যাডমিন কোন ক্লাবের
$dept = $_SESSION['department'];       // ক্লাবটি কোন ডিপার্টমেন্টের অধীনে

// --- Add Event ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $link = $_POST['link'];
    $event_date = $_POST['event_date'];

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['image']['name']);
        $target = '../uploads/events/' . time() . '_' . $filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $image_path = $target;
    }

    // এখানে club_name সেশন থেকে নেওয়া হচ্ছে যাতে কেউ অন্য ক্লাবের নামে ইভেন্ট দিতে না পারে
    $stmt = $conn->prepare("INSERT INTO events (title, club_name, department, description, link, event_date, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $title, $current_club, $dept, $description, $link, $event_date, $image_path);
    $stmt->execute();
}

// --- Update Event ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $id = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $link = $_POST['link'];
    $event_date = $_POST['event_date'];

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['image']['name']);
        $target = '../uploads/events/' . time() . '_' . $filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $image_path = $target;
    }

    // সিকিউরিটি: club_name=? চেক করা হয়েছে যাতে নিজের ক্লাবের বাইরে এডিট না হয়
    if ($image_path) {
        $stmt = $conn->prepare("UPDATE events SET title=?, description=?, link=?, event_date=?, image=? WHERE id=? AND club_name=?");
        $stmt->bind_param("sssssis", $title, $description, $link, $event_date, $image_path, $id, $current_club);
    } else {
        $stmt = $conn->prepare("UPDATE events SET title=?, description=?, link=?, event_date=? WHERE id=? AND club_name=?");
        $stmt->bind_param("ssssis", $title, $description, $link, $event_date, $id, $current_club);
    }

    $stmt->execute();
    header("Location: manage_events.php");
    exit();
}

// --- Delete Event ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM events WHERE id=? AND club_name=?");
    $stmt->bind_param("is", $id, $current_club);
    $stmt->execute();
    header("Location: manage_events.php");
    exit();
}

// --- Edit Mode Fetch ---
$editMode = false;
$editEvent = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM events WHERE id=? AND club_name=?");
    $stmt->bind_param("is", $id, $current_club);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $editMode = true;
        $editEvent = $result->fetch_assoc();
    }
}

// --- Fetch Only This Club's Events ---
$stmt = $conn->prepare("SELECT * FROM events WHERE club_name = ? ORDER BY event_date DESC");
$stmt->bind_param("s", $current_club);
$stmt->execute();
$events = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Club Admin - Manage Events</title>
    <style>
        body { font-family: Poppins; background: #f4f4f4; padding: 30px; }
        form, table { background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, textarea { padding: 8px; margin: 10px 0; width: 100%; box-sizing: border-box; }
        button { background: #14af96; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #004d7a; color: #fff; }
        .back-btn { text-decoration: none; background: #004d7a; color: #fff; padding: 8px 14px; border-radius: 6px; display: inline-block; margin-bottom: 20px; }
        img.event-img { width: 80px; height: auto; border-radius: 4px; }
        .edit { color: green; text-decoration: none; font-weight: bold; }
        .delete { color: red; text-decoration: none; font-weight: bold; margin-left: 10px; }
    </style>
</head>
<body>

    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    <h2>Welcome, <?= htmlspecialchars($current_club) ?> Admin</h2>
    <p>Department: <?= htmlspecialchars($dept) ?></p>

    <!-- Event Form -->
    <form method="post" enctype="multipart/form-data">
        <h3><?= $editMode ? 'Edit Event Details' : 'Post New Event' ?></h3>
        <?php if ($editMode): ?>
            <input type="hidden" name="event_id" value="<?= $editEvent['id'] ?>" />
        <?php endif; ?>

        <label>Event Title</label>
        <input type="text" name="title" placeholder="Enter title" value="<?= $editMode ? $editEvent['title'] : '' ?>" required />
        
        <label>Event Description</label>
        <textarea name="description" rows="4" placeholder="Write details..." required><?= $editMode ? $editEvent['description'] : '' ?></textarea>
        
        <label>Registration/Event Link (Optional)</label>
        <input type="text" name="link" placeholder="https://..." value="<?= $editMode ? $editEvent['link'] : '' ?>" />
        
        <label>Event Date</label>
        <input type="date" name="event_date" value="<?= $editMode ? $editEvent['event_date'] : '' ?>" required />
        
        <label>Event Poster</label>
        <input type="file" name="image" accept="image/*" />

        <button type="submit" name="<?= $editMode ? 'update_event' : 'add_event' ?>">
            <?= $editMode ? 'Update Event' : 'Add Event' ?>
        </button>
        <?php if ($editMode): ?>
            <a href="manage_events.php" style="margin-left:15px; color: #666;">Cancel Edit</a>
        <?php endif; ?>
    </form>

    <!-- Event List -->
    <h3>Your Posted Events</h3>
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Title</th>
                <th>Date</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if($events->num_rows > 0): ?>
                <?php while ($event = $events->fetch_assoc()) { ?>
                    <tr>
                        <td>
                            <?php if (!empty($event['image'])): ?>
                                <img src="<?= $event['image'] ?>" class="event-img">
                            <?php else: ?>
                                <small>No Image</small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($event['title']) ?></td>
                        <td><?= date('d M, Y', strtotime($event['event_date'])) ?></td>
                        <td><?= substr(htmlspecialchars($event['description']), 0, 50) ?>...</td>
                        <td>
                            <a href="?edit=<?= $event['id'] ?>" class="edit">Edit</a>
                            <a href="?delete=<?= $event['id'] ?>" class="delete" onclick="return confirm('Delete this event?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">No events posted yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>