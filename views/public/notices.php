<!DOCTYPE html>
<html>

<head>
    <title>Notice Board - SchoolMS</title>
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

    <main class="public-main">
        <h2>Notice Board</h2>
        <?php if (!empty($notices)) : ?>
            <ul class="list">
                <?php foreach ($notices as $notice) : ?>
                    <li>
                        <strong><?= htmlspecialchars($notice['title']); ?></strong>
                        <span class="meta"><?= htmlspecialchars($notice['created_at']); ?></span>
                        <span class="meta"><?= htmlspecialchars($notice['body'] ?? ''); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>No notices available.</p>
        <?php endif; ?>
    </main>

    <footer class="public-footer">
        <p>&copy; <?= date('Y'); ?> SchoolMS. All rights reserved.</p>
    </footer>
</body>

</html>


