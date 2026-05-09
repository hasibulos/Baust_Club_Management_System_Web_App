<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once('../db.php');

$username = $_SESSION['username'];

// User info fetch
$stmt = $conn->prepare("SELECT username, email, student_id, department, role, profile_pic FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($usern, $email, $student_id, $department, $role, $profile_pic);
$stmt->fetch();
$stmt->close();

// Joined clubs fetch
$joinedClubs = [];
$res = $conn->query("SELECT club_name FROM user_clubs WHERE username = '" . $conn->real_escape_string($username) . "'");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $joinedClubs[] = $row['club_name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Profile - BAUST Club Management</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #051937;
            color: #eee;
            padding: 20px;
        }

        .container {
            max-width: 650px;
            background: rgba(0, 77, 122, 0.85);
            border-radius: 12px;
            padding: 25px 30px;
            margin: auto;
            box-shadow: 0 0 30px rgba(0, 77, 122, 0.9);
            position: relative;
        }

        .profile-pic {
            max-width: 130px;
            border-radius: 0;
            /* ekhane 0 dile square shape hobe */
            border: 3px solid #1de9b6;
            margin-bottom: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            object-fit: cover;
            /* image ke properly fill korbe container */
            aspect-ratio: 1/1;
            /* square aspect ratio maintain korbe */
        }


        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            margin-top: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border-radius: 7px;
            border: 2px solid #1de9b6;
            background: transparent;
            color: #eee;
            outline: none;
            font-size: 1rem;
        }

        input[readonly] {
            background: rgba(30, 30, 30, 0.7);
            cursor: not-allowed;
        }

        button {
            margin-top: 20px;
            background: #1de9b6;
            color: #004d7a;
            padding: 15px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            width: 49%;
            font-size: 1.1rem;
            box-shadow: 0 12px 30px rgba(29, 233, 182, 0.8);
            transition: background-color 0.3s ease;
        }

        button:hover {
            background: #14af96;
        }

        .btn-edit {
            position: absolute;
            top: 20px;
            right: 20px;
            width: auto;
            padding: 12px 24px;
            font-size: 1rem;
            background-color: #007acc;
            /* bright blue */
            color: #fff;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 122, 204, 0.6);
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-edit:hover {
            background-color: #005a99;
            /* darker blue on hover */
            box-shadow: 0 6px 20px rgba(0, 90, 153, 0.7);
        }

        .button-group {
            text-align: right;
        }

        .joined-clubs {
            margin-top: 25px;
        }

        .joined-clubs ul {
            list-style: disc;
            margin-left: 20px;
            padding-left: 0;
        }

        .back-link {
            display: block;
            margin-top: 30px;
            text-align: center;
            font-weight: 600;
            color: #1de9b6;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 style="text-align:center; color:#1de9b6;">My Profile</h2>

        <img src="<?php echo $profile_pic ? '../uploads/' . htmlspecialchars($profile_pic) : '../assets/default-profile.png'; ?>" alt="Profile Picture" class="profile-pic" />

        <form id="profileForm" action="update_profile.php" method="post" enctype="multipart/form-data">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($usern); ?>" readonly>

            <label for="student_id">Student ID</label>
            <input readonly type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>" required>

            <label for="email">Email</label>
            <input readonly type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label for="department">Department</label>
            <input readonly type="text" id="department" name="department" value="<?php echo htmlspecialchars($department); ?>" required>

            <label for="role">Role</label>
            <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($role); ?>" readonly>

            <label for="profile_pic">Upload New Profile Picture</label>
            <input disabled type="file" id="profile_pic" name="profile_pic" accept="image/*" />

            <div class="button-group">
                <button type="button" class="btn-edit" id="editBtn">Edit</button>
                <button type="submit" name="update_profile" id="saveBtn" style="display:none;">Save Changes</button>
            </div>
        </form>

        <div class="joined-clubs">
            <h3>Joined Clubs (<?php echo count($joinedClubs); ?>)</h3>
            <?php if (count($joinedClubs) === 0): ?>
                <p>You have not joined any clubs yet.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($joinedClubs as $club): ?>
                        <li><?php echo htmlspecialchars($club); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <a href="home.php" class="back-link">Back to Home</a>
    </div>

    <script>
        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');
        const form = document.getElementById('profileForm');

        editBtn.addEventListener('click', () => {
            // Enable input fields
            form.querySelectorAll('input[type="text"], input[type="email"], input[type="file"]').forEach(input => {
                if (input.id !== 'username' && input.id !== 'role') {
                    input.readOnly = false;
                    input.disabled = false;
                    input.style.backgroundColor = 'transparent';
                    input.style.cursor = 'auto';
                }
            });
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-block';
        });
    </script>
</body>

</html>