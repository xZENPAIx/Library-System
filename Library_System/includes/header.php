<?php
/**
 * Shared header component for all pages
 * Includes meta tags, CSS links, and basic navigation
 */
require_once __DIR__ . '/../config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Library System') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <?php if (basename($_SERVER['PHP_SELF']) === 'login.php' || basename($_SERVER['PHP_SELF']) === 'signup.php'): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
    <?php endif; ?>
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <a href="<?= BASE_URL ?>/" class="logo">Library<span>System</span></a>
            <nav class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="user-greeting">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                <?php endif; ?>
            </nav>
        </div>
    </header>