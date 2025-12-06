<?php
/**
 * Public Website Header
 * Reusable header component with mobile hamburger menu
 */
?>
<!-- Header -->
<header class="site-header">
    <div class="container">
        <div class="header-content">
            <div class="site-logo">
                <h1><?php echo SITE_NAME; ?></h1>
                <p>Excellence in Education</p>
            </div>
            
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle navigation menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <nav class="main-nav" id="mainNav">
                <!-- Close button inside nav for mobile -->
                <button class="nav-close-btn" onclick="toggleMobileMenu()" aria-label="Close menu">
                    <i class="fas fa-times"></i>
                </button>
                
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/public/index.php">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/about.php">About</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/academics.php">Academics</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/admissions.php">Admissions</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/events.php">Events</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/gallery.php">Gallery</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/notices.php">Notices</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/contact.php">Contact</a></li>
                </ul>
                
                <a href="<?php echo BASE_URL; ?>/login.php" class="login-btn mobile-login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </nav>
            
            <a href="<?php echo BASE_URL; ?>/login.php" class="login-btn desktop-login-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
    </div>
</header>

<style>
/* Mobile Menu Inline Styles - Complement main CSS */
.nav-close-btn {
    display: none;
}

.mobile-login-btn {
    display: none;
}

@media (max-width: 767px) {
    .desktop-login-btn {
        display: none;
    }
    
    .mobile-login-btn {
        display: inline-block;
    }
    
    .nav-close-btn {
        display: flex;
    }
}
</style>

<script>
// Mobile Menu Toggle
function toggleMobileMenu() {
    const nav = document.getElementById('mainNav');
    const toggle = document.getElementById('mobileMenuToggle');
    
    nav.classList.toggle('active');
    toggle.classList.toggle('active');
    
    // Prevent body scroll when menu is open
    if (nav.classList.contains('active')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

document.getElementById('mobileMenuToggle').addEventListener('click', toggleMobileMenu);

// Close menu when clicking a link
document.querySelectorAll('.main-nav a').forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth < 768) {
            toggleMobileMenu();
        }
    });
});

// Handle resize
window.addEventListener('resize', function() {
    if (window.innerWidth >= 768) {
        document.getElementById('mainNav').classList.remove('active');
        document.getElementById('mobileMenuToggle').classList.remove('active');
        document.body.style.overflow = '';
    }
});
</script>
