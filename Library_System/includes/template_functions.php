<?php
require_once __DIR__ . '/../config/constants.php';

/**
 * Output the HTML header
 */
function output_header($title = 'Library System') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> | Library System</title>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/main.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/auth.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/sidebar_fix.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <script src="<?php echo BASE_URL; ?>/assets/js/sidebar_toggle.js" defer></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alertPopup = document.getElementById('alert-popup');
            if (alertPopup) {
                alertPopup.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    alertPopup.style.opacity = '0';
                    setTimeout(() => {
                        alertPopup.remove();
                    }, 500);
                }, 5000); // Hide after 5 seconds
            }
        });
        </script>
    </head>
    <body>
        <header class="header">
            <div class="container header-content">
                <a href="<?php echo BASE_URL; ?>/" class="logo">Library<span>System</span></a>
                <nav class="nav-links">
                    <?php if (is_authenticated()): ?>
                        <a href="<?php echo BASE_URL; ?>/dashboard/<?php echo $_SESSION['user_level']; ?>/">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="<?php echo BASE_URL; ?>/auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/auth/login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="<?php echo BASE_URL; ?>/auth/signup.php">
                            <i class="fas fa-user-plus"></i> Sign Up
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>
        
        <main class="container">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-popup alert-error" id="alert-popup">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert-popup alert-success" id="alert-popup">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
    <?php
}

/**
 * Output the HTML footer
 */
function output_footer() {
    ?>
        </main>
        
        <footer class="footer">
            <div class="container footer-content">
                <div class="footer-links">
                    <a href="<?php echo BASE_URL; ?>/about.php">About</a>
                    <a href="<?php echo BASE_URL; ?>/contact.php">Contact</a>
                    <a href="<?php echo BASE_URL; ?>/privacy.php">Privacy Policy</a>
                    <a href="<?php echo BASE_URL; ?>/terms.php">Terms of Service</a>
                </div>
                <p class="copyright">&copy; <?php echo date('Y'); ?> Library System. All rights reserved.</p>
            </div>
        </footer>
        
        <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
        <?php if (basename($_SERVER['PHP_SELF']) === 'signup.php'): ?>
            <script src="<?php echo BASE_URL; ?>/assets/js/auth.js"></script>
        <?php endif; ?>
    </body>
    </html>
    <?php
}

/**
 * Get the current page URL
 */
function current_url() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
