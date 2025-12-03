<!DOCTYPE html>
<html>

<head>
    <title>Contact Us - SchoolMS</title>
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
        <section class="two-column">
            <div>
                <h2>Contact Information</h2>
                <p>Address: 123 School Lane, Your City</p>
                <p>Phone: +1 (000) 123‑4567</p>
                <p>Email: info@yourschool.com</p>
                <p>Working Hours: Mon–Fri, 8:00 AM – 3:00 PM</p>
            </div>
            <div>
                <h2>Send Us a Message</h2>

                <?php if (!empty($success)): ?>
                    <p class="success"><?= htmlspecialchars($success); ?></p>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <p class="error"><?= htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form method="POST" action="/contact.php" class="auth-container"
                    style="max-width:100%; box-shadow:none; padding:0;">
                    <label>Your Name *</label>
                    <input type="text" name="name" required>

                    <label>Email *</label>
                    <input type="email" name="email" required>

                    <label>Subject</label>
                    <input type="text" name="subject">

                    <label>Message *</label>
                    <textarea name="message" rows="3" required></textarea>

                    <button type="submit">Send Message</button>
                </form>
            </div>
        </section>
    </main>

    <footer class="public-footer">
        <p>&copy; <?= date('Y'); ?> SchoolMS. All rights reserved.</p>
    </footer>
</body>

</html>