<!DOCTYPE html>
<html>

<head>
    <title>Welcome to SchoolMS</title>
    <link rel="stylesheet" href="/style/style.css">
</head>

<body class="public-body">
    <header class="public-header">
        <div class="logo">SchoolMS</div>
        <nav class="public-nav">
            <a href="/index.php">Home</a>
            <a href="/about.php">About</a>
            <a href="/academics.php">Academics</a>
            <a href="/admissions.php">Admissions</a>
            <a href="/news.php">News & Events</a>
            <a href="/gallery.php">Gallery</a>
            <a href="/notices.php">Notices</a>
            <a href="/contact.php">Contact</a>
            <a href="/login.php" class="btn-small">Portal Login</a>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Our School</h1>
            <p>Quality education, holistic development, and a bright future for every student.</p>
            <a href="/admissions.php" class="btn-primary">Apply for Admission</a>
        </div>
    </section>

    <main class="public-main">
        <section class="cards-grid">
            <article class="card">
                <h2>Our Mission</h2>
                <p>To empower students with knowledge, skills, and values to excel in a dynamic world.</p>
            </article>
            <article class="card">
                <h2>Vision</h2>
                <p>To be a leading institution recognized for academic excellence and character building.</p>
            </article>
            <article class="card">
                <h2>Why Choose Us?</h2>
                <p>Experienced faculty, modern classrooms, co‑curricular activities, and a safe campus.</p>
            </article>
        </section>

        <section class="two-column">
            <div>
                <h2>Latest Notices</h2>
                <?php if (!empty($notices)) : ?>
                    <ul class="list">
                        <?php foreach ($notices as $notice) : ?>
                            <li>
                                <strong><?= htmlspecialchars($notice['title']); ?></strong>
                                <span class="meta"><?= htmlspecialchars($notice['created_at']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p>No notices available.</p>
                <?php endif; ?>
            </div>
            <div>
                <h2>Upcoming Events</h2>
                <?php if (!empty($events)) : ?>
                    <ul class="list">
                        <?php foreach ($events as $event) : ?>
                            <li>
                                <strong><?= htmlspecialchars($event['title']); ?></strong>
                                <span class="meta"><?= htmlspecialchars($event['event_date']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p>No upcoming events.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="public-footer">
        <p>&copy; <?= date('Y'); ?> SchoolMS. All rights reserved.</p>
    </footer>
</body>

</html>


