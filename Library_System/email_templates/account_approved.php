<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button {
            background: #3498db;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Account Approved</h2>
        <p>Dear <?= $name ?>,</p>
        <p>Your library account has been approved!</p>
        <p><strong>Username:</strong> <?= $username ?></p>
        <p><a href="<?= $login_url ?>" class="button">Login to Your Account</a></p>
        <p>If you didn't request this, please contact us immediately.</p>
    </div>
</body>
</html>