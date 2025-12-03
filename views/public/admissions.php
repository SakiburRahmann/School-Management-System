<!DOCTYPE html>
<html>

<head>
    <title>Admissions - SchoolMS</title>
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
        <section class="cards-grid">
            <article class="card">
                <h2>Admission Rules</h2>
                <p>Admission is granted based on eligibility, merit, and availability of seats as per school policy.</p>
            </article>
            <article class="card">
                <h2>Required Documents</h2>
                <p>Birth certificate, previous school records, passport-sized photographs, and identity proof of
                    guardians.</p>
            </article>
        </section>

        <section>
            <h2>Online Admission Form</h2>

            <?php if (!empty($success)): ?>
                <p class="success"><?= htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <p class="error"><?= htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" action="/admissions.php" class="auth-container" style="max-width:600px;">
                <h2 style="margin-top:0;">Apply for Admission</h2>
                <label>Student Name *</label>
                <input type="text" name="student_name" required>

                <label>Class Applied For *</label>
                <input type="text" name="class_applied" required>

                <label>Guardian Name</label>
                <input type="text" name="guardian_name">

                <label>Guardian Phone</label>
                <input type="text" name="guardian_phone">

                <label>Message</label>
                <textarea name="message" rows="3"></textarea>

                <button type="submit">Submit Application</button>
            </form>
        </section>
    </main>

    <footer class="public-footer">
        <p>&copy; <?= date('Y'); ?> SchoolMS. All rights reserved.</p>
    </footer>
</body>

</html>