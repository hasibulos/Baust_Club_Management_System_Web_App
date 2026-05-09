<?php
require_once('../includes/session_check.php');
require_once('../includes/role_check.php');
require_role(['SUPER_ADMIN']);
require_once('../includes/db.php');

// Static department list
$deptList = ['AIS', 'BBA', 'CIVIL', 'CSE', 'EEE', 'English', 'IPE', 'ME', "MATH"];

// Add Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $department = $_POST['department'];
    $status = $_POST['status'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Ensure department exists in departments table
    $checkDept = $conn->prepare("SELECT COUNT(*) FROM departments WHERE name = ?");
    $checkDept->bind_param("s", $department);
    $checkDept->execute();
    $checkDept->bind_result($deptExists);
    $checkDept->fetch();
    $checkDept->close();

    if ($deptExists == 0) {
        $insertDept = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
        $insertDept->bind_param("s", $department);
        $insertDept->execute();
    }

    // Check if department already has an admin
    $check = $conn->prepare("SELECT COUNT(*) FROM admins WHERE department = ? AND role = 'DEPARTMENT_ADMIN'");
    $check->bind_param("s", $department);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if ($count > 0 && $role === 'DEPARTMENT_ADMIN') {
        echo "<p style='color:red; font-weight:bold;'>❌ This department already has an admin.</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO admins (name, username, email, role, department, status, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $username, $email, $role, $department, $status, $password);
        $stmt->execute();
    }
}

// Update Admin
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
//     $id = $_POST['admin_id'];
//     $name = $_POST['name'];
//     $username = $_POST['username'];
//     $email = $_POST['email'];
//     $role = $_POST['role'];
//     $department = $_POST['department'];
//     $status = $_POST['status'];

//     $stmt = $conn->prepare("UPDATE admins SET name=?, username=?, email=?, role=?, department=?, status=? WHERE id=?");
//     $stmt->bind_param("ssssssi", $name, $username, $email, $role, $department, $status, $id);
//     $stmt->execute();
//     header("Location: manage_admins.php");
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
    $id = $_POST['admin_id'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $department = $_POST['department'];
    $status = $_POST['status'];
    $newPassword = $_POST['password'] ?? '';

    if (!empty($newPassword)) {
        // ✅ If new password provided, hash and update
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET name=?, username=?, email=?, role=?, department=?, status=?, password=? WHERE id=?");
        $stmt->bind_param("sssssssi", $name, $username, $email, $role, $department, $status, $passwordHash, $id);
    } else {
        // ✅ If no new password, keep old one
        $stmt = $conn->prepare("UPDATE admins SET name=?, username=?, email=?, role=?, department=?, status=? WHERE id=?");
        $stmt->bind_param("ssssssi", $name, $username, $email, $role, $department, $status, $id);
    }

    $stmt->execute();
    header("Location: manage_admins.php");
    exit();
}

// Delete Admin
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM admins WHERE id = $id");
    header("Location: manage_admins.php");
    exit();
}

// Edit Mode
$editMode = false;
$editAdmin = null;
if (isset($_GET['edit'])) {
    $editMode = true;
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM admins WHERE id = $id");
    $editAdmin = $result->fetch_assoc();
}

// Fetch Admins
$admins = $conn->query("SELECT * FROM admins ORDER BY role DESC, name ASC");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Admins</title>
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

        .back-btn {
            text-decoration: none;
            background: #004d7a;
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .edit {
            color: green;
            text-decoration: none;
        }

        .delete {
            color: red;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

    <?php if ($editMode): ?>
        <h2>Edit Admin</h2>
        <form method="post">
            <input type="hidden" name="admin_id" value="<?= $editAdmin['id'] ?>" />
            <input type="text" name="name" placeholder="Full Name" value="<?= $editAdmin['name'] ?>" required />
            <input type="text" name="username" placeholder="Username" value="<?= $editAdmin['username'] ?>" required />
            <input type="email" name="email" placeholder="Email" value="<?= $editAdmin['email'] ?>" required />
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="SUPER_ADMIN" <?= $editAdmin['role'] === 'SUPER_ADMIN' ? 'selected' : '' ?>>SUPER_ADMIN</option>
                <option value="DEPARTMENT_ADMIN" <?= $editAdmin['role'] === 'DEPARTMENT_ADMIN' ? 'selected' : '' ?>>DEPARTMENT_ADMIN</option>
            </select>
            <select name="department" required>
                <option value="">Select Department</option>
                <?php foreach ($deptList as $dept): ?>
                    <option value="<?= $dept ?>" <?= $editAdmin['department'] === $dept ? 'selected' : '' ?>><?= $dept ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" required>
                <option value="">Select Status</option>
                <option value="active" <?= $editAdmin['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $editAdmin['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
            <button type="submit" name="update_admin">Update Admin</button>
            <a href="manage_admins.php" style="margin-left:10px; color:#004d7a;">Cancel</a>
        </form>
    <?php else: ?>
        <h2>Add Admin</h2>
        <form method="post">
            <input type="text" name="name" placeholder="Full Name" required />
            <input type="text" name="username" placeholder="Username" required />
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Password" required />
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="SUPER_ADMIN">SUPER_ADMIN</option>
                <option value="DEPARTMENT_ADMIN">DEPARTMENT_ADMIN</option>
            </select>
            <select name="department" required>
                <option value="">Select Department</option>
                <?php foreach ($deptList as $dept): ?>
                    <option value="<?= $dept ?>"><?= $dept ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" required>
                <option value="">Select Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <button type="submit" name="add_admin">Add Admin</button>
        </form>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Department</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($admin = $admins->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($admin['name']) ?></td>
                    <td><?= htmlspecialchars($admin['username']) ?></td>
                    <td><?= htmlspecialchars($admin['email']) ?></td>
                    <td><?= htmlspecialchars($admin['role']) ?></td>
                    <td><?= htmlspecialchars($admin['department']) ?></td>
                    <td><?= $admin['status'] === 'active' ? '🟢 Active' : '🔴 Inactive' ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="?edit=<?= $admin['id'] ?>" class="edit">Edit</a>
                            <a href="?delete=<?= $admin['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this admin?')">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>

</html>