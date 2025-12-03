<!DOCTYPE html>
<html>

<head>
    <title>Gallery - SchoolMS</title>
    <link rel="stylesheet" href="/style/style.css">
    <style>
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .gallery-item {
            background: #ffffff;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        }

        .gallery-item img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            display: block;
        }

        .gallery-item .caption {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
        }
    </style>
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
        <h2>Photo Gallery</h2>

        <?php if (!empty($images)): ?>
            <div class="gallery-grid">
                <?php foreach ($images as $img): ?>
                    <div class="gallery-item">
                        <img src="<?= htmlspecialchars($img['image_path']); ?>"
                            alt="<?= htmlspecialchars($img['caption'] ?? ''); ?>">
                        <div class="caption">
                            <?= htmlspecialchars($img['caption'] ?? ''); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No gallery images uploaded yet.</p>
        <?php endif; ?>
    </main>

    <footer class="public-footer">
        <p>&copy; <?= date('Y'); ?> SchoolMS. All rights reserved.</p>
    </footer>
</body>

</html>