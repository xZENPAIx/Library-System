<?php
// config/constants.php

// Color scheme following 60-30-10 rule
define('PRIMARY_COLOR', '#17321A');
define('SECONDARY_COLOR', '#00954F');
define('ACCENT_COLOR', '#FDC530');
define('LIGHT_ACCENT', '#FCD83D');
define('BRIGHT_ACCENT', '#FFE800');
define('DARK_GREEN', '#146939');

// System settings - UPDATE THIS TO YOUR ACTUAL BASE URL
define('BASE_URL', 'http://localhost/Library_System');
define('MAX_BOOKS_PER_STUDENT', 5);
define('LOAN_PERIOD_DAYS', 14);
define('FINE_PER_DAY', 10);
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour