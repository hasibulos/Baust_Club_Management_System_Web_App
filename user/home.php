<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once('../db.php');

$role = $_SESSION['role'];
$showProfile = !in_array($role, ['SUPER_ADMIN', 'DEPT_ADMIN']);
$showDashboard = in_array($role, ['SUPER_ADMIN', 'DEPT_ADMIN']);

// Fetch clubs for display on home (all clubs)
$clubs = $conn->query("SELECT * FROM clubs ORDER BY name ASC");

// Fetch distinct departments for submenu
$departmentsResult = $conn->query("SELECT DISTINCT department FROM clubs ORDER BY department ASC");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Clubs - BAUST Club Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
    <style>
        /* CSS same to same rakha hoyeche */
        * {
            box-sizing: border-box;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #051937, #004d7a);
            color: #eee;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(5, 25, 55, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem 2rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.4);
            z-index: 999;
        }

        nav ul {
            display: flex;
            gap: 1.4rem;
            align-items: center;
        }

        nav ul li {
            position: relative;
        }

        nav ul li a,
        nav ul li button {
            color: #1de9b6;
            font-weight: 600;
            font-size: 1rem;
            background: none;
            padding: 0;
            border: none;
            cursor: pointer;
        }

        nav ul li:hover>ul.dropdown,
        nav ul li:focus-within>ul.dropdown {
            display: block;
        }

        ul.dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 140px;
            background: #003359;
            border-radius: 6px;
            display: none;
            flex-direction: column;
            padding: 0.3rem 0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.6);
        }

        ul.dropdown li {
            width: 100%;
            padding: 0 1rem;
        }

        ul.dropdown li a,
        ul.dropdown li button {
            display: block;
            padding: 8px 0;
            color: #1de9b6;
            font-weight: 500;
            background: none;
            border: none;
            text-align: left;
            width: 100%;
            cursor: pointer;
        }

        ul.dropdown li a:hover,
        ul.dropdown li button:hover {
            background: #1de9b6;
            color: #004d7a;
            border-radius: 4px;
        }

        ul.dropdown li ul.dropdown {
            top: 0;
            left: 100%;
            margin-left: 6px;
        }

        main {
            max-width: 1200px;
            margin: 6rem auto 2rem;
            padding: 0 1.2rem;
        }

        h1 {
            color: #1de9b6;
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 700;
            border-bottom: 2px solid #14af96;
            padding-bottom: 0.5rem;
        }

        #searchInput {
            width: 100%;
            max-width: 400px;
            padding: 10px 16px;
            font-size: 1rem;
            border-radius: 8px;
            border: 2px solid #1de9b6;
            margin-bottom: 1.5rem;
            outline: none;
            background: rgba(0, 77, 122, 0.2);
            color: #eee;
        }

        #searchInput::placeholder {
            color: #bbb;
        }

        ul.club-list {
            list-style: none;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }

        li.club-item {
            background: rgba(0, 77, 122, 0.85);
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0, 77, 122, 0.8);
            display: flex;
            flex-direction: column;
            height: 400px;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }

        li.club-item:hover {
            box-shadow: 0 0 30px #1de9b6;
        }

        .club-image {
            flex: 7;
            width: 100%;
            height: 70%;
            object-fit: cover;
            object-position: center top;
        }

        .club-info {
            flex: 1;
            padding: 0.6rem 1rem;
            background: rgba(0, 77, 122, 0.95);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .club-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: #1de9b6;
            margin-bottom: 0.3rem;
            text-decoration: none;
        }

        .club-moderator {
            font-weight: 600;
            color: #76eec6;
            margin-top: auto;
            font-size: 1rem;
        }

        .join-btn {
            background-color: #14af96;
            padding: 6px 12px;
            font-size: 0.9rem;
            border-radius: 6px;
            border: none;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 12px;
        }

        .join-btn:hover {
            background-color: #0b6d66;
        }

        .announcement-section {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1.2rem;
        }

        .announcement-title {
            color: #004d7a;
            font-weight: 800;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            text-align: center;
            background-color: #e0f7f4;
            padding: 0.8rem 1rem;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            display: inline-block;
            width: 100%;
        }

        .announcement-card {
            background: #ffffff;
            padding: 1.2rem 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease;
        }

        .announcement-card:hover {
            transform: translateY(-3px);
        }

        .announcement-card h3 {
            color: #14af96;
            margin-bottom: 0.5rem;
            font-size: 1.3rem;
        }

        .announcement-card p {
            color: #333;
            margin-bottom: 0.8rem;
            line-height: 1.6;
        }

        .announcement-card small {
            color: #555;
            font-size: 0.9rem;
            font-style: italic;
            display: block;
            margin-top: 0.6rem;
            padding-top: 0.3rem;
            border-top: 1px solid #eee;
            text-align: right;
        }

        .no-results {
            display: none;
            text-align: center;
            color: #ffeb3b;
            padding: 20px;
            font-size: 1.2rem;
            grid-column: 1 / -1;
        }

        @media (max-width: 650px) {
            ul.club-list {
                grid-template-columns: 1fr;
                padding: 0 10px;
            }

            main {
                padding: 0 3px;
            }
        }
    </style>
</head>

<body>
    <nav>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="events.php">Events</a></li>
            <li><a href="alumni.php">Alumni</a></li>
            <li tabindex="0">
                <button aria-haspopup="true" aria-expanded="false" class="dropdown-toggle">Clubs ▼</button>
                <ul class="dropdown" aria-label="Clubs categories">
                    <li><a href="clubs.php?filter=type&value=cultural">Cultural</a></li>
                    <li><a href="clubs.php?filter=type&value=technical">Technical</a></li>
                    <li><a href="clubs.php?filter=type&value=sports">Sports</a></li>
                    <li><button class="filter-btn" data-filter-type="all">All</button></li>
                    <li tabindex="0">
                        <button aria-haspopup="true" aria-expanded="false" class="dropdown-toggle">Department-wise ▼</button>
                        <ul class="dropdown" aria-label="Departments">
                            <?php
                            $departmentsResult->data_seek(0);
                            while ($dep = $departmentsResult->fetch_assoc()) : ?>
                                <li><a href="clubs.php?filter=department&value=<?php echo urlencode(strtolower($dep['department'])); ?>"><?php echo htmlspecialchars($dep['department']); ?></a></li>
                            <?php endwhile; ?>
                        </ul>
                    </li>
                </ul>
            </li>
            <?php if (!in_array($role, ['SUPER_ADMIN', 'DEPARTMENT_ADMIN'])): ?>
                <li><a href="profile.php">My Profile</a></li>
            <?php endif; ?>

            <?php
            $roleFolder = ($role === 'SUPER_ADMIN') ? 'admin' : ($role === 'DEPARTMENT_ADMIN' ? 'department_admin' : '');
            ?>
            <?php if ($roleFolder): ?>
                <li><a href="../<?= $roleFolder ?>/dashboard.php">Back to Dashboard</a></li>
            <?php endif; ?>

            <li><a href="../auth/logout.php" style="color:#cc2f31;">Logout</a></li>
        </ul>
    </nav>

    <main>
        <h1>Clubs at BAUST</h1>

        <!-- Search Input -->
        <input type="text" id="searchInput" placeholder="Search clubs or alumni directory keywords..." aria-label="Search clubs" />

        <?php
        $important = $conn->query("SELECT * FROM announcements WHERE is_important = 1 ORDER BY created_at DESC");
        ?>

        <section class="announcement-section">
            <h2 class="announcement-title">📢 Important Announcements</h2>
            <?php if ($important->num_rows > 0): ?>
                <?php while ($row = $important->fetch_assoc()): ?>
                    <div class="announcement-card">
                        <h3><?= htmlspecialchars($row['title']) ?></h3>
                        <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                        <small>
                            Posted by <strong><?= htmlspecialchars($row['posted_by']) ?></strong>
                            on <?= date("F j, Y, g:i:s A", strtotime($row['created_at'])) ?>
                        </small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; color:#999; font-style:italic;">No important announcements at the moment.</p>
            <?php endif; ?>
        </section>

        <ul class="club-list" id="clubList">
            <div id="noResults" class="no-results">😞 No matches found in clubs or alumni directory.</div>
            <?php while ($club = $clubs->fetch_assoc()) : ?>
                <li class="club-item"
                    data-name="<?php echo strtolower($club['name']); ?>"
                    data-dept="<?php echo strtolower($club['department']); ?>"
                    data-type="<?php echo strtolower($club['type']); ?>">

                    <?php if (!empty($club['image']) && file_exists($club['image'])): ?>
                        <img src="<?php echo htmlspecialchars($club['image']); ?>" alt="Club Image" class="club-image">
                    <?php else: ?>
                        <img src="../uploads/clubs/default_club.jpg" alt="Default Image" class="club-image">
                    <?php endif; ?>

                    <div class="club-info">
                        <a href="<?php echo htmlspecialchars($club['website']); ?>" class="club-name"><?php echo htmlspecialchars($club['name']); ?></a>
                        <div class="club-details"><strong>Type:</strong> <?php echo htmlspecialchars($club['type']); ?></div>
                        <div class="club-details"><strong>Department:</strong> <?php echo htmlspecialchars($club['department']); ?></div>
                        <div class="club-moderator">Moderator/President: <?php echo htmlspecialchars($club['moderator']); ?></div>
                        <form method="post" action="join_club.php" style="margin-top:1rem;">
                            <input type="hidden" name="club_name" value="<?php echo htmlspecialchars($club['name']); ?>">
                            <button class="join-btn" type="submit">Join Request</button>
                        </form>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    </main>

    <div style="text-align: center; margin: 2rem 0;">
        <a href="alumni.php" style="background: #1de9b6; color: #004d7a; padding: 12px 30px; border-radius: 30px; font-weight: bold; font-size: 1.2rem; box-shadow: 0 4px 15px rgba(29, 233, 182, 0.4); display: inline-block;">View BAUST Alumni Directory</a>
    </div>

    <section style="max-width:1200px; margin: 4rem auto 3rem auto; padding: 0 1.2rem; text-align: center;">
        <h2 style="color:#1de9b6; font-weight:700; margin-bottom: 1.5rem;">About the Developers</h2>
        <div style="display:flex; justify-content: center; gap: 1.5rem; flex-wrap: wrap;">
            <a href="about.php?dev=1" style="text-decoration:none;">
                <div style="background: #14af96; color:white; padding: 1.5rem 2rem; border-radius: 12px; width: 220px; box-shadow: 0 5px 15px rgba(20,175,150,0.6); font-weight:bold; font-size:1.1rem;">
                    Hasibul Hasib
                </div>
            </a>
            <a href="about.php?dev=2" style="text-decoration:none;">
                <div style="background: #14af96; color:white; padding: 1.5rem 2rem; border-radius: 12px; width: 220px; box-shadow: 0 5px 15px rgba(20,175,150,0.6); font-weight:bold; font-size:1.1rem;">
                    Mst. Jugnu Khatun
                </div>
            </a>
            <a href="about.php?dev=3" style="text-decoration:none;">
                <div style="background: #14af96; color:white; padding: 1.5rem 2rem; border-radius: 12px; width: 220px; box-shadow: 0 5px 15px rgba(20,175,150,0.6); font-weight:bold; font-size:1.1rem;">
                    Most. Arifa Akter ALLo
                </div>
            </a>
        </div>
    </section>

    <script>
        const searchInput = document.getElementById('searchInput');
        const clubList = document.getElementById('clubList');
        const noResults = document.getElementById('noResults');
        const filterButtons = document.querySelectorAll('.filter-btn');

        let currentFilterType = 'all';
        let currentFilterValue = '';

        function updateUI() {
            const searchTerm = searchInput.value.trim().toLowerCase();
            const items = Array.from(clubList.getElementsByClassName('club-item'));
            let visibleCount = 0;

            items.forEach(item => {
                const name = item.getAttribute('data-name');
                const dept = item.getAttribute('data-dept');
                const type = item.getAttribute('data-type');

                let matchesFilter = true;
                if (currentFilterType === 'type' && currentFilterValue) {
                    matchesFilter = (type === currentFilterValue);
                } else if (currentFilterType === 'department' && currentFilterValue) {
                    matchesFilter = (dept === currentFilterValue);
                }

                let matchesSearch = (name.includes(searchTerm) || dept.includes(searchTerm) || type.includes(searchTerm));

                if (matchesFilter && matchesSearch) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            noResults.style.display = (visibleCount === 0) ? 'block' : 'none';
        }

        filterButtons.forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                currentFilterType = btn.dataset.filterType || 'all';
                currentFilterValue = btn.dataset.filterValue || '';
                updateUI();
            });
        });

        searchInput.addEventListener('input', updateUI);
    </script>
</body>

</html>