<?php
require_once('../includes/session_check.php');
require_once('../includes/role_check.php');
require_role(['SUPER_ADMIN']);
require_once('../includes/db.php');

// Add Club
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_club'])) {
    $name = $_POST['name'];
    $department = $_POST['department'];
    $moderator = $_POST['moderator'];
    $type = $_POST['type'];
    $website = $_POST['website'];

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['image']['name']);
        $target = '../uploads/clubs/' . time() . '_' . $filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $image_path = $target;
    }

    $stmt = $conn->prepare("INSERT INTO clubs (name, department, moderator, type, website, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $department, $moderator, $type, $website, $image_path);
    $stmt->execute();
}

// Update Club
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_club'])) {
    $id = $_POST['club_id'];
    $name = $_POST['name'];
    $moderator = $_POST['moderator'];
    $website = $_POST['website'];
    $department = $_POST['department'];
    $type = $_POST['type'];

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES['image']['name']);
        $target = '../uploads/clubs/' . time() . '_' . $filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $image_path = $target;
    }

    if ($image_path) {
        $stmt = $conn->prepare("UPDATE clubs SET name=?, moderator=?, website=?, department=?, type=?, image=? WHERE id=?");
        $stmt->bind_param("ssssssi", $name, $moderator, $website, $department, $type, $image_path, $id);
    } else {
        $stmt = $conn->prepare("UPDATE clubs SET name=?, moderator=?, website=?, department=?, type=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $moderator, $website, $department, $type, $id);
    }

    $stmt->execute();
    header("Location: manage_clubs.php");
    exit();
}

// Delete Club
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM clubs WHERE id = $id");
    header("Location: manage_clubs.php");
    exit();
}

// Edit Mode
$editMode = false;
$editClub = null;
if (isset($_GET['edit'])) {
    $editMode = true;
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM clubs WHERE id = $id");
    $editClub = $result->fetch_assoc();
}

// Fetch Clubs
$clubs = $conn->query("SELECT * FROM clubs");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Clubs</title>
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
        select {
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
    </style>
</head>

<body>
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

    <?php if ($editMode): ?>
        <h2>Edit Club</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="club_id" value="<?= $editClub['id'] ?>" />
            <input type="text" name="name" placeholder="Club Name" value="<?= $editClub['name'] ?>" required />
            <input type="text" name="moderator" placeholder="Moderator Name" value="<?= $editClub['moderator'] ?>" required />
            <input type="text" name="website" placeholder="Club Website URL" value="<?= $editClub['website'] ?>" />
            <select name="department" required>
                <option value="">Select Department</option>
                <?php
                $deptList = ['EEE', 'CSE', 'ME', 'English', 'BBA', 'CIVIL', 'IPE', 'AIS', 'MATH'];
                foreach ($deptList as $dept) {
                    $selected = ($editClub['department'] === $dept) ? 'selected' : '';
                    echo "<option value='$dept' $selected>$dept</option>";
                }
                ?>
            </select>
            <select name="type" required>
                <option value="">Select Type</option>
                <?php
                $types = ['cultural', 'technical', 'sports'];
                foreach ($types as $type) {
                    $selected = ($editClub['type'] === $type) ? 'selected' : '';
                    echo "<option value='$type' $selected>" . ucfirst($type) . "</option>";
                }
                ?>
            </select>
            <input type="file" name="image" accept="image/*" />
            <button type="submit" name="update_club">Update Club</button>
            <a href="manage_clubs.php" style="margin-left:10px; color:#004d7a;">Cancel</a>
        </form>
    <?php else: ?>
        <h2>Add Club</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Club Name" required />
            <input type="text" name="moderator" placeholder="Moderator Name" required />
            <input type="text" name="website" placeholder="Club Website URL" />
            <select name="department" required>
                <option value="">Select Department</option>
                <?php
                $deptList = ['EEE', 'CSE', 'ME', 'English', 'BBA', 'CIVIL', 'IPE', 'AIS', 'MATH'];
                foreach ($deptList as $dept) {
                    echo "<option value='$dept'>$dept</option>";
                }
                ?>
            </select>
            <select name="type" required>
                <option value="">Select Type</option>
                <option value="cultural">Cultural</option>
                <option value="technical">Technical</option>
                <option value="sports">Sports</option>
            </select>
            <input type="file" name="image" accept="image/*" />
            <button type="submit" name="add_club">Add Club</button>
        </form>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Moderator</th>
                <th>Department</th>
                <th>Type</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($club = $clubs->fetch_assoc()) { ?>
                <tr>
                    <td>
                        <?php if (!empty($club['image']) && file_exists($club['image'])): ?>
                            <img src="<?= $club['image'] ?>" alt="Club Image" style="width:80px; height:60px; object-fit:cover; border-radius:6px;">
                        <?php else: ?>
                            <img src="../uploads/clubs/default_club.jpg" alt="Default Image" style="width:80px; height:60px; object-fit:cover; border-radius:6px;">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($club['name']) ?></td>
                    <td><?= htmlspecialchars($club['moderator']) ?></td>
                    <td><?= htmlspecialchars($club['department']) ?></td>
                    <td><?= htmlspecialchars($club['type']) ?></td>
                    <td>
                        <a href="?edit=<?= $club['id'] ?>" class="edit">Edit</a>
                        <a href="?delete=<?= $club['id'] ?>" class="delete">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>