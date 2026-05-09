<?php
require_once('../includes/session_check.php');
require_once('../includes/role_check.php');
require_role(['SUPER_ADMIN', 'DEPARTMENT_ADMIN']);
require_once('../includes/db.php');

$current_user_role = $_SESSION['role'];
$current_user_dept = $_SESSION['department'] ?? null;

// --- Edit-er jonno data fetch kora ---
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $res = $conn->query("SELECT * FROM alumni WHERE id = $edit_id");
    $edit_data = $res->fetch_assoc();

    // Security: Dept Admin jeno onno dept er data edit mode-e na nite pare
    if ($current_user_role === 'DEPARTMENT_ADMIN' && $edit_data['department'] !== $current_user_dept) {
        die("Unauthorized access.");
    }
}

// --- Logic: Add / Update Alumni ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $batch = $_POST['batch'] ?? '';
    $dept = $_POST['department'] ?? $current_user_dept;
    $job = $_POST['current_job'] ?? '';
    $email = $_POST['email'] ?? '';
    $linkedin = $_POST['linkedin_url'] ?? '';

    if ($current_user_role === 'DEPARTMENT_ADMIN' && $dept !== $current_user_dept) {
        die("Error: You can only manage alumni from your own department.");
    }

    $image_path = $_POST['old_image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target = '../uploads/alumni/' . time() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) $image_path = $target;
    }

    if (isset($_POST['add_alumni'])) {
        $stmt = $conn->prepare("INSERT INTO alumni (name, batch, department, current_job, email, linkedin_url, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $batch, $dept, $job, $email, $linkedin, $image_path);
        $stmt->execute();
    } elseif (isset($_POST['update_alumni'])) {
        $id = $_POST['alumni_id'];

        if ($current_user_role === 'DEPARTMENT_ADMIN') {
            $check = $conn->prepare("SELECT department FROM alumni WHERE id = ?");
            $check->bind_param("i", $id);
            $check->execute();
            $check->bind_result($alumni_dept);
            $check->fetch();
            $check->close();
            if ($alumni_dept !== $current_user_dept) die("Unauthorized action.");
        }

        $stmt = $conn->prepare("UPDATE alumni SET name=?, batch=?, department=?, current_job=?, email=?, linkedin_url=?, image=? WHERE id=?");
        $stmt->bind_param("sssssssi", $name, $batch, $dept, $job, $email, $linkedin, $image_path, $id);
        $stmt->execute();
    }
    header("Location: manage_alumni.php");
    exit();
}

// --- Logic: Delete Alumni ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($current_user_role === 'DEPARTMENT_ADMIN') {
        $check = $conn->prepare("SELECT department FROM alumni WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $check->bind_result($alumni_dept);
        $check->fetch();
        $check->close();
        if ($alumni_dept !== $current_user_dept) die("Unauthorized action.");
    }
    $conn->query("DELETE FROM alumni WHERE id = $id");
    header("Location: manage_alumni.php");
    exit();
}

// --- Query: Fetch List ---
if ($current_user_role === 'SUPER_ADMIN') {
    $alumni_list = $conn->query("SELECT * FROM alumni ORDER BY batch DESC");
} else {
    $stmt = $conn->prepare("SELECT * FROM alumni WHERE department = ? ORDER BY batch DESC");
    $stmt->bind_param("s", $current_user_dept);
    $stmt->execute();
    $alumni_list = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Alumni</title>
    <style>
        body {
            font-family: Poppins;
            background: #f4f4f4;
            padding: 20px;
        }

        .container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            max-width: 1000px;
            margin: auto;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #004d7a;
            color: white;
        }

        .alumni-img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }

        .btn-edit {
            color: #14af96;
            text-decoration: none;
            margin-right: 10px;
            font-weight: bold;
        }

        .btn-delete {
            color: #cc2f31;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="dashboard.php" style="text-decoration: none; color: #004d7a;">← Back to Dashboard</a>
        <h2>Manage Alumni (<?= htmlspecialchars($current_user_role) ?>)</h2>

        <form method="post" enctype="multipart/form-data" style="background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
            <h3><?= $edit_data ? "Edit Alumni Information" : "Add New Alumni" ?></h3>

            <?php if ($edit_data): ?>
                <input type="hidden" name="alumni_id" value="<?= $edit_data['id'] ?>">
                <input type="hidden" name="old_image" value="<?= $edit_data['image'] ?>">
            <?php endif; ?>

            <input type="text" name="name" placeholder="Full Name" value="<?= $edit_data['name'] ?? '' ?>" required>
            <input type="text" name="batch" placeholder="Batch (e.g. 5th)" value="<?= $edit_data['batch'] ?? '' ?>" required>

            <?php if ($current_user_role === 'SUPER_ADMIN'): ?>
                <select name="department" required>
                    <?php
                    $depts = ['CSE', 'EEE', 'CE', 'ME', 'IPE'];
                    echo '<option value="">Select Department</option>';
                    foreach ($depts as $d) {
                        $selected = (isset($edit_data) && $edit_data['department'] == $d) ? 'selected' : '';
                        echo "<option value='$d' $selected>$d</option>";
                    }
                    ?>
                </select>
            <?php else: ?>
                <input type="text" name="department" value="<?= htmlspecialchars($current_user_dept) ?>" readonly>
            <?php endif; ?>

            <input type="text" name="current_job" placeholder="Current Job/Company" value="<?= $edit_data['current_job'] ?? '' ?>">
            <input type="email" name="email" placeholder="Email" value="<?= $edit_data['email'] ?? '' ?>">
            <input type="text" name="linkedin_url" placeholder="LinkedIn URL" value="<?= $edit_data['linkedin_url'] ?? '' ?>">

            <div style="margin-top: 5px;">
                <label style="font-size: 0.8rem; color: #666;">Profile Image (Leave blank to keep old)</label>
                <input type="file" name="image" accept="image/*">
            </div>

            <?php if ($edit_data): ?>
                <button type="submit" name="update_alumni" style="background: #004d7a; color: #fff; border: none; cursor: pointer; margin-top: 10px;">Update Alumni Info</button>
                <a href="manage_alumni.php" style="display:block; text-align:center; margin-top:10px; color:#666; font-size:0.9rem;">Cancel Edit</a>
            <?php else: ?>
                <button type="submit" name="add_alumni" style="background: #14af96; color: #fff; border: none; cursor: pointer; margin-top: 10px;">Add Alumni</button>
            <?php endif; ?>
        </form>

        <hr>

        <h3>Alumni List</h3>
        <table>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Batch</th>
                <th>Dept</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $alumni_list->fetch_assoc()): ?>
                <tr>
                    <td><img src="<?= $row['image'] ?: '../assets/default-profile.png' ?>" class="alumni-img"></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['batch']) ?></td>
                    <td><?= htmlspecialchars($row['department']) ?></td>
                    <td>
                        <a href="?edit=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                        <a href="?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>

</html>