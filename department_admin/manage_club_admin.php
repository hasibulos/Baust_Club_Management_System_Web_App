<?php
require_once('../includes/session_check.php');
require_once('../includes/db.php');

if ($_SESSION['role'] !== 'DEPARTMENT_ADMIN') {
    header("Location: ../auth/login.php");
    exit();
}

$dept = $_SESSION['department'];
$success = "";
$error = "";

// বর্তমান ফাইলের নাম অটোমেটিক ডিটেক্ট করা (যাতে Not Found এরর না আসে)
$current_file = basename($_SERVER['PHP_SELF']);

// --- Edit Mode Fetch ---
$editMode = false;
$editAdmin = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND department = ? AND role = 'CLUB_ADMIN'");
    $stmt->bind_param("is", $id, $dept);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $editMode = true;
        $editAdmin = $result->fetch_assoc();
    }
}

// --- Add or Update Club Admin ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_club_admin']) || isset($_POST['update_club_admin']))) {
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $club_name = $_POST['club_name'];

    if (isset($_POST['update_club_admin'])) {
        $id = $_POST['admin_id'];
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, password=?, club_name=? WHERE id=? AND department=?");
            $stmt->bind_param("ssssis", $full_name, $username, $password, $club_name, $id, $dept);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, club_name=? WHERE id=? AND department=?");
            $stmt->bind_param("sssis", $full_name, $username, $club_name, $id, $dept);
        }
        $stmt->execute();
        header("Location: $current_file?msg=updated");
        exit();
    } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $checkUser = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkUser->bind_param("s", $username);
        $checkUser->execute();
        if ($checkUser->get_result()->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (full_name, username, password, role, department, club_name) VALUES (?, ?, ?, 'CLUB_ADMIN', ?, ?)");
            $stmt->bind_param("sssss", $full_name, $username, $password, $dept, $club_name);
            $stmt->execute();
            $success = "Created successfully!";
        }
    }
}

// --- Delete Club Admin ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND department = ? AND role = 'CLUB_ADMIN'");
    $stmt->bind_param("is", $id, $dept);
    $stmt->execute();
    header("Location: $current_file?msg=deleted");
    exit();
}

// ক্লাব লিস্ট এবং অ্যাডমিন লিস্ট ফেচ করা
$clubs = $conn->prepare("SELECT name FROM clubs WHERE department = ?");
$clubs->bind_param("s", $dept);
$clubs->execute();
$club_list = $clubs->get_result();

$admins = $conn->prepare("SELECT * FROM users WHERE department = ? AND role = 'CLUB_ADMIN'");
$admins->bind_param("s", $dept);
$admins->execute();
$admin_list = $admins->get_result();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Club Admins</title>
    <style>
        body {
            font-family: Poppins;
            background: #f4f4f4;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        form,
        table {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        input,
        select {
            padding: 10px;
            margin: 10px 0;
            width: 100%;
            box-sizing: border-box;
        }

        button {
            background: #14af96;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .cancel-btn {
            color: #666;
            text-decoration: none;
            margin-left: 15px;
            font-size: 0.9rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #004d7a;
            color: white;
        }

        .back-btn {
            text-decoration: none;
            background: #004d7a;
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .pass-container {
            position: relative;
        }

        .toggle-pass {
            position: absolute;
            right: 10px;
            top: 18px;
            cursor: pointer;
            color: #666;
            font-size: 0.8rem;
            user-select: none;
        }

        .msg {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        <h2>Manage Club Admins (<?= htmlspecialchars($dept) ?>)</h2>

        <?php if ($success || isset($_GET['msg'])): ?>
            <div class='msg success'>Action Completed Successfully!</div>
        <?php endif; ?>
        <?php if ($error) echo "<div class='msg error'>$error</div>"; ?>

        <form method="post">
            <h3><?= $editMode ? 'Edit Club Admin' : 'Create New Club Admin' ?></h3>
            <?php if ($editMode): ?>
                <input type="hidden" name="admin_id" value="<?= $editAdmin['id'] ?>">
            <?php endif; ?>

            <input type="text" name="full_name" placeholder="Full Name" value="<?= $editMode ? htmlspecialchars($editAdmin['full_name']) : '' ?>" required>
            <input type="text" name="username" placeholder="Username" value="<?= $editMode ? htmlspecialchars($editAdmin['username']) : '' ?>" required>

            <div class="pass-container">
                <input type="password" name="password" id="passInput" placeholder="<?= $editMode ? 'Leave blank to keep current' : 'Password' ?>" <?= $editMode ? '' : 'required' ?>>
                <span class="toggle-pass" id="toggleBtn" onclick="togglePassword()">Show</span>
            </div>

            <label>Select Club:</label>
            <select name="club_name" required>
                <option value="">-- Select a Club --</option>
                <?php
                $club_list->data_seek(0);
                while ($row = $club_list->fetch_assoc()):
                ?>
                    <option value="<?= $row['name'] ?>" <?= ($editMode && $editAdmin['club_name'] == $row['name']) ? 'selected' : '' ?>>
                        <?= $row['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" name="<?= $editMode ? 'update_club_admin' : 'add_club_admin' ?>">
                <?= $editMode ? 'Update Admin' : 'Create Admin' ?>
            </button>
            <?php if ($editMode): ?>
                <a href="<?= $current_file ?>" class="cancel-btn">Cancel Edit</a>
            <?php endif; ?>
        </form>

        <h3>Existing Club Admins</h3>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Club</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($admin_list->num_rows > 0): ?>
                    <?php while ($admin = $admin_list->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($admin['full_name']) ?></td>
                            <td><?= htmlspecialchars($admin['username']) ?></td>
                            <td><?= htmlspecialchars($admin['club_name']) ?></td>
                            <td>
                                <a href="?edit=<?= $admin['id'] ?>" style="color:green; font-weight:bold;">Edit</a> |
                                <a href="?delete=<?= $admin['id'] ?>" style="color:red; font-weight:bold;" onclick="return confirm('Delete this admin?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">No admins found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function togglePassword() {
            var x = document.getElementById("passInput");
            var btn = document.getElementById("toggleBtn");
            if (x.type === "password") {
                x.type = "text";
                btn.innerHTML = "Hide";
            } else {
                x.type = "password";
                btn.innerHTML = "Show";
            }
        }
    </script>
</body>

</html>