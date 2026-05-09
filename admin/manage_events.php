<?php
require_once('../includes/session_check.php');
require_once('../includes/role_check.php');
require_role(['SUPER_ADMIN']);
require_once('../includes/db.php');

// Add Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $club_name = $_POST['club_name'];
    $department = $_POST['department'];
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

    $stmt = $conn->prepare("INSERT INTO events (title, club_name, department, description, link, event_date, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $title, $club_name, $department, $description, $link, $event_date, $image_path);
    $stmt->execute();
}

// Update Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $id = $_POST['event_id'];
    $title = $_POST['title'];
    $club_name = $_POST['club_name'];
    $department = $_POST['department'];
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

    if ($image_path) {
        $stmt = $conn->prepare("UPDATE events SET title=?, club_name=?, department=?, description=?, link=?, event_date=?, image=? WHERE id=?");
        $stmt->bind_param("sssssssi", $title, $club_name, $department, $description, $link, $event_date, $image_path, $id);
    } else {
        $stmt = $conn->prepare("UPDATE events SET title=?, club_name=?, department=?, description=?, link=?, event_date=? WHERE id=?");
        $stmt->bind_param("ssssssi", $title, $club_name, $department, $description, $link, $event_date, $id);
    }
    $stmt->execute();
    header("Location: manage_events.php");
    exit();
}

// Delete Event
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM events WHERE id = $id");
    header("Location: manage_events.php");
    exit();
}

// Edit Mode
$editMode = false;
$editEvent = null;
if (isset($_GET['edit'])) {
    $editMode = true;
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM events WHERE id = $id");
    $editEvent = $result->fetch_assoc();
}

// Fetch Events
$events = $conn->query("SELECT * FROM events");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Events</title>
    <style>
        body {
            font-family: Poppins;
            background: #f4f4f4;
            padding: 30px;
        }

        form,
        table {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        input,
        select,
        textarea {
            padding: 8px;
            margin: 10px 0;
            width: 100%;
        }

        button {
            background: #004d7a;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #004d7a;
            color: #fff;
        }

        a.delete {
            color: red;
            text-decoration: none;
            margin-left: 10px;
        }

        a.edit {
            color: green;
            text-decoration: none;
            margin-right: 10px;
        }

        .back-btn {
            text-decoration: none;
            background: #004d7a;
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 20px;
        }

        img.event-img {
            width: 100px;
            height: auto;
            border-radius: 6px;
        }
    </style>
</head>

<body>
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    <h2><?= $editMode ? 'Edit Event' : 'Add Event' ?></h2>

    <form method="post" enctype="multipart/form-data">
        <?php if ($editMode): ?>
            <input type="hidden" name="event_id" value="<?= $editEvent['id'] ?>" />
        <?php endif; ?>
        <input type="text" name="title" placeholder="Event Title" value="<?= $editMode ? $editEvent['title'] : '' ?>" required />
        <input type="text" name="club_name" placeholder="Club Name" value="<?= $editMode ? $editEvent['club_name'] : '' ?>" required />
        <select name="department" required>
            <option value="">Select Department</option>
            <?php
            $deptList = ['EEE', 'CSE', 'ME', 'English', 'BBA', 'CIVIL', 'IPE', 'AIS', 'MATH'];
            foreach ($deptList as $dept) {
                $selected = ($editMode && $editEvent['department'] === $dept) ? 'selected' : '';
                echo "<option value='$dept' $selected>$dept</option>";
            }
            ?>
        </select>
        <textarea name="description" placeholder="Event Description" required><?= $editMode ? $editEvent['description'] : '' ?></textarea>
        <input type="text" name="link" placeholder="Event Link (optional)" value="<?= $editMode ? $editEvent['link'] : '' ?>" />
        <input type="date" name="event_date" value="<?= $editMode ? $editEvent['event_date'] : '' ?>" required />
        <input type="file" name="image" accept="image/*" />
        <button type="submit" name="<?= $editMode ? 'update_event' : 'add_event' ?>">
            <?= $editMode ? 'Update Event' : 'Add Event' ?>
        </button>
        <?php if ($editMode): ?>
            <a href="manage_events.php" style="margin-left:10px; color:#004d7a;">Cancel</a>
        <?php endif; ?>
    </form>

    <h2>All Events</h2>
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Title</th>
                <th>Club</th>
                <th>Department</th>
                <th>Date</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($event = $events->fetch_assoc()) { ?>
                <tr>
                    <td>
                        <?php if (!empty($event['image'])): ?>
                            <img src="<?= $event['image'] ?>" class="event-img" alt="Event Image">
                        <?php else: ?>
                            <span>No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($event['title']) ?></td>
                    <td><?= htmlspecialchars($event['club_name']) ?></td>
                    <td><?= htmlspecialchars($event['department']) ?></td>
                    <td><?= htmlspecialchars($event['event_date']) ?></td>
                    <td><?= htmlspecialchars($event['description']) ?></td>
                    <td>
                        <a href="?edit=<?= $event['id'] ?>" class="edit">Edit</a>
                        <a href="?delete=<?= $event['id'] ?>" class="delete">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>

</html>