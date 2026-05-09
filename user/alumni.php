<?php
require_once('../includes/session_check.php');
require_once('../includes/db.php');

// Filter logic
$dept_filter = isset($_GET['dept']) ? $_GET['dept'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT * FROM alumni WHERE 1=1";
if ($dept_filter) $query .= " AND department = '$dept_filter'";
if ($search) $query .= " AND (name LIKE '%$search%' OR batch LIKE '%$search%')";
$query .= " ORDER BY batch DESC";

$alumni_list = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Alumni Directory | BAUST</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #051937;
            color: #fff;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .search-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .search-container input,
        .search-container select {
            padding: 10px;
            border-radius: 5px;
            border: none;
            margin: 5px;
        }

        .alumni-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: auto;
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid rgba(20, 175, 150, 0.3);
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: #14af96;
        }

        .card img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #14af96;
            margin-bottom: 15px;
        }

        .card h3 {
            margin: 10px 0 5px;
            color: #14af96;
        }

        .card p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #ccc;
        }

        .linkedin-btn {
            display: inline-block;
            margin-top: 15px;
            background: #0077b5;
            color: #fff;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .back-link {
            color: #14af96;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="header">
        <a href="home.php" class="back-link">← Back to Home</a>
        <h1>BAUST Alumni Directory</h1>
        <p>Connecting the past with the present</p>
    </div>

    <div class="search-container">
        <form method="GET">
            <input type="text" name="search" placeholder="Search by name or batch..." value="<?= htmlspecialchars($search) ?>">
            <select name="dept">
                <option value="">All Departments</option>
                <?php
                $depts = ['CSE', 'EEE', 'ME', 'IPE', 'CE', 'BBA', 'English'];
                foreach ($depts as $d) {
                    $sel = ($dept_filter == $d) ? 'selected' : '';
                    echo "<option value='$d' $sel>$d</option>";
                }
                ?>
            </select>
            <button type="submit" style="padding: 10px 20px; background: #14af96; border: none; color: white; border-radius: 5px; cursor: pointer;">Filter</button>
        </form>
    </div>

    <div class="alumni-grid">
        <?php if ($alumni_list->num_rows > 0): ?>
            <?php while ($row = $alumni_list->fetch_assoc()): ?>
                <div class="card">
                    <?php
                    $img = !empty($row['image']) ? $row['image'] : '../assets/default-profile.png';
                    // Fix path if saved as ../uploads/... in admin side
                    $display_img = str_replace('../', '../', $img);
                    ?>
                    <img src="<?= $display_img ?>" alt="Alumni Photo">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p><strong><?= $row['department'] ?></strong> | Batch: <?= $row['batch'] ?></p>
                    <p><i><?= htmlspecialchars($row['current_job']) ?></i></p>

                    <?php if ($row['linkedin_url']): ?>
                        <a href="<?= $row['linkedin_url'] ?>" target="_blank" class="linkedin-btn">Connect on LinkedIn</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; grid-column: 1 / -1;">No alumni found matching your criteria.</p>
        <?php endif; ?>
    </div>

</body>

</html>